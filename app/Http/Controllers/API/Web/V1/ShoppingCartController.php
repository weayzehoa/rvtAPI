<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryOrderItem as OrderItemDB;
use App\Models\iCarryShoppingCart as ShoppingCartDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\ProductImage as ProductImageDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\GateSystemSetting as SystemSettingDB;
use Validator;
use Illuminate\Validation\Rule;
use App\Traits\ProductAvailableDate;
use App\Traits\producttockDays;
use App\Traits\LanguagePack;
use DB;

class ShoppingCartController extends Controller
{
    use ProductAvailableDate, LanguagePack;

    protected $userId;

    public function __construct()
    {
        $this->vendorTable = $orderTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $this->productTable = $orderTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $this->productModelTable = $orderTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        //將request()放入變數中
        $request = request();
        $this->request = request();

        //檢查有無登入
        $this->userId = null;
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }elseif(!empty($this->request->icarry_uid)){
            $this->userId = $this->request->icarry_uid;
        }
        $userId = $this->userId;

        //定義語言及檔案路徑變數
        $this->lang = '';
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');

        //定義Rules
        $this->Rules = [
            'session' => [Rule::requiredIf(function () use ($userId) {
                return (empty($userId));
            }),'uuid','max:40'],
            'domain' => 'required|string|max:50',
            'lang'  => 'nullable|in:en,jp,kr,th|string|max:5',
        ];

        $this->indexRules = array_merge($this->Rules,[
            'from_country_id' => 'required|in:1,5|numeric',
            'to_country_id' => 'required|numeric',
            'shipping_method_id' => [Rule::requiredIf(function () use ($request) {
                return ($request->from_country_id == 1 && $request->to_country_id == 1);
            }),'numeric','in:1,2,3,4,5,6'],
            'take_time' =>'required_if:to_country_id,1|date',
            'promotion_code'  => 'nullable|in:jznxstt,VISA,TWPAY',
            'points' => 'nullable|numeric|in:1,0',
        ]);

        $this->createUpdateRules = array_merge($this->Rules,[
            'product_model_id' => 'required|numeric',
            'quantity'  => 'required|numeric|min:1',
        ]);

        $this->amountRules = array_merge($this->indexRules,[
            'shoppingCartIds' => 'nullable|array',
            'shoppingCartIds.*' => 'integer',
        ]);

        $this->checkCodeRules = [
            'from_country_id' => 'required_if:promotion_code,VISA|numeric',
            'to_country_id' => 'required_if:promotion_code,VISA|numeric',
            'promotion_code'  => 'required|string|max:20',
        ];

        $this->imageRule = null;
        for($i=1;$i<=5;$i++){
            $this->imageRule .= " WHEN product.new_photo$i is not null THEN CONCAT('$this->awsFileUrl',product.new_photo$i) ";
        }
        for($i=1;$i<=4;$i++){
            $this->imageRule .= " WHEN product.photo$i is not null THEN product.photo$i ";
        }
    }

    public function index()
    {
        if (Validator::make($this->request->all(), $this->indexRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->indexRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->indexRules))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        //使用者有登入，將所有相同session的資料歸屬給user
        !empty($this->userId) ? $this->updateToUser() : '';
        //抓取購物車資料
        $getCart = $this->getCart($type='index');
        //統計可結帳與不可結帳資料
        !empty($getCart['available']) ? $total['availableTotal'] = count($getCart['available']) : $total['availableTotal'] = 0;
        !empty($getCart['unAvailable']) ? $total['unAvailableTotal'] = count($getCart['unAvailable']) : $total['unAvailableTotal'] = 0;
        //計算可結帳資料
        $calculation = $this->calculation($getCart['available']);
        foreach($getCart['available'] as $cart){
            unset($cart->amount_gross_weight);
        }
        $data = array_merge(['calculation' => $calculation],$getCart);
        return $this->appDataResponse('Success', 0, $total, $data);
    }

    public function store(Request $request)
    {
        if (Validator::make($this->request->all(), $this->createUpdateRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->createUpdateRules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->createUpdateRules))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        // 找是否已存在購物車, 有則數量累加
        $shoppingCart = ShoppingCartDB::where('product_model_id',$this->product_model_id)
        ->where(function($query){
            $query = $query->where('user_id',$this->userId)
                ->orWhere('session',$this->session);
        })->first();
        !empty($this->userId) ? $data['user_id'] = $this->userId : '';
        $product = ProductModelDB::join($this->productTable,$this->productTable.'.id',$this->productModelTable.'.product_id')
        ->where($this->productModelTable.'.id',$this->product_model_id)->select([$this->productTable.'.vendor_id'])->first();
        !empty($product) ? $data['vendor_id'] = $product->vendor_id : '';
        if(!empty($shoppingCart)){
            $data['quantity'] = $shoppingCart->quantity + $quantity;
            $shoppingCart->update($data);
        }else{
            $shoppingCart = ShoppingCartDB::create($data);
        }
        $id = $shoppingCart->id;
        $shoppingCart = ShoppingCartDB::join('product_model','product_model.id','shopping_cart.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->select([
            'shopping_cart.id',
            'shopping_cart.product_model_id',
            'shopping_cart.quantity',
            'product.category_id',
        ])->find($id);
        $shoppingCart->category_id == 17 ? $remove = 'nonTicket' : $remove = 'ticket';
        $this->removeShoppingCart($remove); //若是票券則移除非票券, 非票券移除票券
        unset($shoppingCart->category_id);
        return $this->appDataResponse('Success', 0, '新增成功', $shoppingCart, 200);
    }

    public function show($id)
    {
        if (Validator::make($this->request->all(), $this->Rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->Rules)->errors(), 400);
        }
        $this->lang = $this->request->lang;
        $shoppingCart = ShoppingCartDB::join('product_model','product_model.id','shopping_cart.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->join('vendor','vendor.id','product.vendor_id');
        $shoppingCart = $shoppingCart->select([
            'shopping_cart.id',
            'shopping_cart.product_model_id',
            'shopping_cart.quantity',
            'vendor.id as vendor_id',
            'vendor.name as vendor_name',
            'product.name as product_name',
            'product.id as product_id',
            'product.fake_price',
            'product.price',
            'product.model_type',
            DB::raw("(CASE WHEN model_type != 1 THEN product.model_name ELSE null END) as model_name"),
            DB::raw("(CASE $this->imageRule ELSE null END) as image"),
            // 'image' => ProductImageDB::whereColumn('product.id', 'product_images.product_id')->where('is_on',1)
            // ->select(DB::raw("(CASE WHEN filename is not null THEN (CONCAT('$this->awsFileUrl',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
        ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $shoppingCart = $shoppingCart->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendor.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.name END) as product_name"),
                DB::raw("(CASE WHEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.model_name END) as model_name"),
            ]);
        }
        $shoppingCart = $shoppingCart->findOrFail($id);

        if($shoppingCart->model_type != 2){
            unset($shoppingCart->model_name);
        }
        if($shoppingCart->model_type != 1){
            $shoppingCart->product_model = ProductModelDB::where('product_id',$shoppingCart->product_id);
            $shoppingCart->product_model = $shoppingCart->product_model->select([
                'id as product_model_id',
                'name',
                DB::raw("(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as outOffStock")
            ]);
            if(!empty($this->lang) && in_array($this->lang,$this->langs)){
                $shoppingCart->product_model = $shoppingCart->product_model->addSelect([
                    DB::raw("(CASE WHEN name_{$this->lang} != '' THEN name_{$this->lang} ELSE (CASE WHEN name_en != '' THEN name_en ELSE name END) END) as name"),
                ]);
            }
            $shoppingCart->product_model = $shoppingCart->product_model->get();
        }
        return $this->appDataResponse('Success', 0, '購物車資料', $shoppingCart, 200);
    }

    public function update(Request $request, $id)
    {
        if (Validator::make($this->request->all(), $this->createUpdateRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->createUpdateRules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if (in_array($key, array_keys($this->createUpdateRules))) {
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        $shoppingCart = ShoppingCartDB::findOrFail($id);
        !empty($this->userId) ? $data['user_id'] = $this->userId : '';

        $checkShoppingCart = ShoppingCartDB::where('product_model_id',$this->product_model_id)
        ->where('id','!=',$id)
        ->where(function($query){
            $query = $query->where('user_id',$this->userId)
                ->orWhere('session',$this->session);
        })->first();

        if(!empty($checkShoppingCart)){
            return $this->appCodeResponse('Error', 0, "購物車中已有該商品，無法再修改成該商品。" , 200);
        }
        $product = ProductModelDB::join($this->productTable,$this->productTable.'.id',$this->productModelTable.'.product_id')
        ->where($this->productModelTable.'.id',$this->product_model_id)->select([$this->productTable.'.vendor_id'])->first();
        !empty($product) ? $data['vendor_id'] = $vendor->vendor_id : '';
        $shoppingCart->update($data);

        $shoppingCart = ShoppingCartDB::join('product_model','product_model.id','shopping_cart.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->select([
            'shopping_cart.id',
            'shopping_cart.user_id',
            'shopping_cart.product_model_id',
            'shopping_cart.session',
            'shopping_cart.domain',
            'product.category_id',
        ])->find($id);
        $shoppingCart->category_id == 17 ? $remove = 'nonTicket' : $remove = 'ticket';
        $this->removeShoppingCart($remove); //若是票券則移除非票券, 非票券移除票券
        unset($shoppingCart->category_id);
        return $this->appDataResponse('Success', 0, '修改成功' , $shoppingCart);
    }

    public function destroy($id)
    {
        if (Validator::make($this->request->all(), $this->Rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->Rules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if (in_array($key, array_keys($this->Rules))) {
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        $shoppingCart = ShoppingCartDB::where([['session',$this->session],['domain',$this->domain]]);
        !empty($this->userId) ? $shoppingCart = $shoppingCart->where('user_id',$this->userId) : '';
        $shoppingCart = $shoppingCart->findOrFail($id);
        $shoppingCart->delete();
        return $this->appDataResponse('Success', 0, '刪除成功' , null);
    }

    public function total()
    {
        if (Validator::make($this->request->all(), $this->Rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->Rules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if (in_array($key, array_keys($this->Rules))) {
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        $shoppingCart = ShoppingCartDB::where([['session',$this->session],['domain',$this->domain]]);
        !empty($this->userId) ? $shoppingCart = $shoppingCart->orWhere('user_id',$this->userId) : '';
        $count = $shoppingCart->count();
        return $this->appDataResponse('Success', 0, '購物車數量' , $count);
    }

    public function amount()
    {
        if (Validator::make($this->request->all(), $this->amountRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->amountRules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if (in_array($key, array_keys($this->amountRules))) {
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        $getCart = $this->getCart();
        $calculation = $this->calculation($getCart);
        return $this->appDataResponse('Success', 0, '計算完成。' , $calculation);
    }

    public function checkPromoCode()
    {
        if (Validator::make($this->request->all(), $this->checkCodeRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->checkCodeRules)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->checkCodeRules))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        $check = 0;
        $nowtime=intval(date("YmdHis"));
        if(strtoupper($this->request->promotion_code)==="VISA" && $nowtime<=20211231235959 && $nowtime>=20201221000000){
            $this->request->from_country_id == 1 && $this->request->country_id != 1 ? $check = 1 : $check = 0;
        }
        strtolower($this->request->promotion_code==="jznxstt") && $nowtime<=20200921000000 ? $check = 1 : '';
        strtoupper($this->request->promotion_code)==="TWPAY" && $nowtime<=20211031235959 && $nowtime>=20210601000000 ? $check = 1 : '';
        if($check == 1){
            return $this->appDataResponse('Success', 0, '促銷代碼可用' , $check);
        }
        return $this->appDataResponse('Error', 9, '促銷代碼無效或過期' , $check, 400);
    }

    private function getCart($type = null)
    {
        $data['available'] = [];
        $data['unAvailable'] = [];
        $fromCountry = CountryDB::find($this->from_country_id)->name;
        $toCountry = CountryDB::find($this->to_country_id)->name;
        if($this->to_country_id == 1 && $this->shipping_method_id == 4){
            if($this->from_country_id == $this->to_country_id){
                $this->shipping_method_id = 6;
            }elseif($this->from_country_id != 1){
                $this->shipping_method_id = 5;
            }
        }
        //寄送其他地區全部改成寄送海外
        ($this->from_country_id == 5 && $this->to_country_id != 5) || ($this->from_country_id == 1 && $this->to_country_id != 1) ? $this->shipping_method_id = 4 : '';
        $shoppingCarts = ShoppingCartDB::join('product_model','product_model.id','shopping_cart.product_model_id')
            ->join('product','product.id','product_model.product_id')
            ->join('vendor','vendor.id','product.vendor_id');
        if(!empty($this->userId)) {
            $shoppingCarts = $shoppingCarts->where([['shopping_cart.user_id',$this->userId],['shopping_cart.domain',$this->domain]])
            ->orWhere([['shopping_cart.session',$this->session],['shopping_cart.domain',$this->domain]]);
        }elseif(!empty($this->session)){
            $shoppingCarts = $shoppingCarts->where([['shopping_cart.session',$this->session],['shopping_cart.domain',$this->domain]]);
        }else{ //下面主要給第三方使用
            if(!empty($this->userId)){
                $shoppingCarts = $shoppingCarts->where([['shopping_cart.domain',$this->domain],['shopping_cart.user_id',$this->userId]]);
            }else{
                if ($type == 'index') {
                    return $data;
                }else{
                    return $data['available'];
                }
            }
        }
        !empty($this->shoppingCartIds) ? $shoppingCarts = $shoppingCarts->whereIn('shopping_cart.id',$this->shoppingCartIds) : '';
        $shoppingCarts = $shoppingCarts->select([
            'shopping_cart.id',
            'shopping_cart.user_id',
            'shopping_cart.product_model_id',
            'product.id as product_id',
            'shopping_cart.quantity',
            'product.category_id',
            'product.price',
            'product.model_type',
            'product.airplane_days',
            'product.hotel_days',
            'product.vendor_earliest_delivery_date',
            DB::raw('(CASE WHEN product.model_type = 2 THEN (CONCAT(product_model.name,product.model_name)) WHEN product.model_type = 3 THEN product_model.name ELSE null END) as model_name'),
            DB::raw('( product.price * shopping_cart.quantity ) as amount_price'),
            DB::raw('( product.gross_weight * shopping_cart.quantity ) as amount_gross_weight'),
            DB::raw("(CASE WHEN vendor.is_on != 1 OR product.status != 1 OR product_model.quantity < shopping_cart.quantity THEN 1 ELSE 0 END) as outOffStock"),
            DB::raw("(CASE WHEN vendor.is_on = 1 AND product.status = 1 AND product_model.quantity > shopping_cart.quantity AND '$this->from_country_id' = product.from_country_id AND find_in_set('$this->to_country_id',product.allow_country_ids) AND find_in_set('$this->shipping_method_id', IF(product.shipping_methods = '' OR product.shipping_methods IS NULL,'1,2,3,4,5,6',product.shipping_methods) ) THEN 1 ELSE 0 END) as available"),
        ]);
        if($this->to_country_id == 1){
            $shoppingCarts = $shoppingCarts->addSelect([
                DB::raw("(CASE WHEN '$this->to_country_id' = 1 and '$this->shipping_method_id' = 1 THEN product.airplane_days WHEN '$this->to_country_id' = 1 and '$this->shipping_method_id' != 1 THEN product.hotel_days ELSE null END) as stockDays"),
            ]);
        }
        if($type == 'index'){
            $shoppingCarts = $shoppingCarts->addSelect([
                'vendor.name as vendor_name',
                'product.name as product_name',
                'product.unable_buy',
                DB::raw("(CASE $this->imageRule ELSE null END) as image"),
                // 'image' => ProductImageDB::whereColumn('product.id', 'product_images.product_id')->where('product_images.is_on',1)
                //     ->select(DB::raw("(CASE WHEN filename != '' THEN (CONCAT('$this->awsFileUrl',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
                DB::raw("(CASE WHEN vendor.is_on != 1 OR product.status != 1 OR product_model.quantity < shopping_cart.quantity THEN '庫存不足' WHEN '$this->from_country_id' != product.from_country_id THEN (SELECT CONCAT('此商品只可由',name,'發貨') from countries where countries.id = product.from_country_id limit 1) WHEN NOT find_in_set('$this->to_country_id',product.allow_country_ids) THEN '無法寄送{$toCountry}' WHEN NOT find_in_set('$this->shipping_method_id', IF(product.shipping_methods = '' OR product.shipping_methods IS NULL,'1,2,3,4,5,6',product.shipping_methods) ) THEN '此商品不支援目前所選的物流方式，請更換物流' END) as unAvailableReason"),
            ]);
            if (!empty($this->lang) && in_array($this->lang, $this->langs)) {
                $fromCountryEn = CountryDB::find($this->from_country_id)->name_en;
                $toCountryEn = CountryDB::find($this->to_country_id)->name_en;
                $translate = $this->translate(['庫存不足','最快可提貨日','無法寄送{0}','此商品只可由{0}發貨','此商品不支援目前所選的物流方式，請更換物流']);
                $translate['無法寄送{0}'] = str_replace('{0}', $toCountryEn, $translate['無法寄送{0}']);
                $translate['此商品只可由{0}發貨'] = str_replace('{0}', "',name_en,'", $translate['此商品只可由{0}發貨']);
                $shoppingCarts = $shoppingCarts->addSelect([
                    DB::raw("(CASE WHEN product.model_type = 2 THEN ( CONCAT( (CASE WHEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT model_name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.model_name END), ( CASE WHEN product_model.name_$this->lang != '' THEN product_model.name_$this->lang ELSE (CASE WHEN product_model.name_en != '' THEN product_model.name_en ELSE product_model.name END) END ) ) ) WHEN product.model_type = 3 THEN (CASE WHEN product_model.name_$this->lang != '' THEN product_model.name_$this->lang ELSE (CASE WHEN product_model.name_en != '' THEN product_model.name_en ELSE product_model.name END) END) END) as model_name"),
                    DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendor.name END) as vendor_name"),
                    DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.name END) as product_name"),
                    DB::raw("(CASE WHEN (SELECT unable_buy from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT unable_buy from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT unable_buy from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT unable_buy from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.unable_buy END) as unable_buy"),
                    DB::raw("(CASE WHEN vendor.is_on != 1 OR product.status != 1 OR product_model.quantity < shopping_cart.quantity THEN '{$translate['庫存不足']}' WHEN '$this->from_country_id' != product.from_country_id THEN (SELECT CONCAT('{$translate['此商品只可由{0}發貨']}') from countries where countries.id = product.from_country_id limit 1) WHEN NOT find_in_set('$this->to_country_id',product.allow_country_ids) THEN '{$translate['無法寄送{0}']}' WHEN NOT find_in_set('$this->shipping_method_id', IF(product.shipping_methods = '' OR product.shipping_methods IS NULL,'1,2,3,4,5,6',product.shipping_methods) ) THEN '{$translate['此商品不支援目前所選的物流方式，請更換物流']}' END) as unAvailableReason"),
                ]);
            }
        }
        $shoppingCarts = $shoppingCarts->orderBy('shopping_cart.create_time','asc')->get();
        if(count($shoppingCarts) > 0){
            foreach($shoppingCarts as $shoppingCart){
                //如果使用者有登入，將沒有user_id的購物車資料更新
                if(!empty($this->userId) && $shoppingCart->user_id == '' or $shoppingCart->user_id == null){
                    $shoppingCart->update(['user_id' => $this->userId]);
                }
                unset($shoppingCart->user_id);
                if ($shoppingCart->available == 1) {
                    if($shoppingCart->category_id != 17){ //非票券則需判斷最快可提貨日是否小於預定提貨日，否則將可購買改為不可購買
                        if ($this->from_country_id == 1 && $this->to_country_id == 1) { //寄送台灣
                            $shoppingCart->take_tiem = $this->take_time;
                            $shoppingCart->payTime = date('Ymd');
                            $shoppingCart->min_pay_time = date('Y-m-d');
                            $shoppingCart->min_pay_time = date('Y-m-d');
                            $this->shipping_method_id == 1 ? $shoppingCart->max_days = $shoppingCart->airplane_days : $shoppingCart->max_days = $shoppingCart->hotel_days;
                            $this->shipping_method_id == 1 ? $shoppingCart->max_receiver_key_time = $this->take_time : $shoppingCart->max_receiver_key_time = null; //機場提貨時間
                            $atLeastDay = $this->getProductAvailableDate($shoppingCart);
                            unset($shoppingCart->take_tiem);
                            unset($shoppingCart->payTime);
                            unset($shoppingCart->min_pay_time);
                            unset($shoppingCart->min_pay_time);
                            unset($shoppingCart->max_days);
                            unset($shoppingCart->max_receiver_key_time);
                            if (strtotime($atLeastDay) > strtotime($this->take_time)) {
                                if($type == 'index'){
                                    $shoppingCart->unAvailableReason = "最快可提貨日：".$atLeastDay;
                                    $shoppingCart->unable_buy ? $shoppingCart->unAvailableReason = $shoppingCart->unAvailableReason.' , '.$shoppingCart->unable_buy : '';
                                    !empty($this->lang) && in_array($this->lang, $this->langs) ? $shoppingCart->unAvailableReason = $translate['最快可提貨日'].' '.$atLeastDay : '';
                                    !empty($this->lang) && in_array($this->lang, $this->langs) ? $shoppingCart->unAvailableReason = $shoppingCart->unAvailableReason.(!empty($shoppingCart->unable_buy) ? ' , '.$shoppingCart->unable_buy : '') : '';
                                }
                                $shoppingCart->available = 0;
                                $data['unAvailable'][] = $shoppingCart;
                            } else {
                                unset($shoppingCart->unAvailableReason);
                                $data['available'][] = $shoppingCart;
                            }
                        }
                    }else{
                        unset($shoppingCart->unAvailableReason);
                        $data['available'][] = $shoppingCart;
                    }
                    unset($shoppingCart->stockDays);
                    unset($shoppingCart->unable_buy);
                } else {
                    if($type == 'index'){
                        $shoppingCart->unable_buy ? $shoppingCart->unAvailableReason = $shoppingCart->unAvailableReason.' , '.$shoppingCart->unable_buy : '';
                        !empty($this->lang) && in_array($this->lang, $this->langs) ? $shoppingCart->unAvailableReason = $shoppingCart->unAvailableReason.(!empty($shoppingCart->unable_buy) ? ' , '.$shoppingCart->unable_buy : '') : '';
                    }
                    unset($shoppingCart->stockDays);
                    unset($shoppingCart->unable_buy);
                    $data['unAvailable'][] = $shoppingCart;
                }
            }
        }
        if ($type == 'index') {
            return $data;
        }else{
            return $data['available'];
        }
    }

    private function calculation($availableProduct = [])
    {
        $data['freeShipping'] = $data['availableQuantity'] = $data['productAmount'] = $data['totalGrossWeight'] = $data['parcelTax'] = $data['points'] = $data['discount'] = $data['shippingFee'] = $data['totalAmount'] = 0;
        $productSoldCountry = $shippingMethods = null;
        $fromCountry = CountryDB::where('id',$this->from_country_id)->first();
        $toCountry = CountryDB::where('id',$this->to_country_id)->first();
        if($this->shipping_method_id == 1){
            $shippingMethods = '當地機場';
        }elseif($this->shipping_method_id == 2){
            $shippingMethods = '當地旅店';
        }elseif($this->shipping_method_id == 6){
            $shippingMethods = '當地地址';
        }else{
            !empty($toCountry) ? $shippingMethods = $toCountry->name : '';
        }
        !empty($fromCountry) ? $productSoldCountry = $fromCountry->name : '';
        $shippingFee = ShippingFeeDB::where([['product_sold_country',$productSoldCountry],['shipping_methods',$shippingMethods]])->first();
        if(!empty($shippingFee)){
            $systemSetting = SystemSettingDB::first();
            if(count($availableProduct) > 0){
                foreach ($availableProduct as $cart){
                    $data['availableQuantity'] += $cart->quantity;
                    $data['totalGrossWeight'] += ($cart->amount_gross_weight * $systemSetting->gross_weight_rate / 1000);
                    $data['productAmount'] += $cart->amount_price;
                    $cart->category_id == 17 ? $isTicket = 1 : $isTicket = 0;
                }
                if($isTicket == 0){
                    //跨境稅
                    $data['parcelTax'] = round($data['productAmount'] * ($shippingFee->tax_rate / 100));
                    $data['parcelTaxRate'] = $shippingFee->tax_rate;
                    //運費
                    $shippingFee->shipping_type == 'base' ? $data['shippingFee'] = $shippingFee->shipping_base_price : $data['shippingFee'] = round(ceil($data['totalGrossWeight']) * $shippingFee->shipping_kg_price); //未滿一公斤以一公斤計算 ex: 1.3kg = 2kg
                    //免運計算 (商品金額-購物金-折扣)
                    ($data['productAmount']+$data['points']+$data['discount']) >= $shippingFee->free_shipping ? $data['shippingFee'] = 0 : '';
                    //免運門檻
                    $data['freeShipping'] = $shippingFee->free_shipping;
                    //每公斤運費
                    $shippingFee->shipping_type == 'kg' ? $data['shippingKgFee'] = $shippingFee->shipping_kg_price : $data['shippingKgFee'] = 0;
                    $data['totalGrossWeight'] = round($data['totalGrossWeight'],2);
                }
                //購物金
                !empty($this->points) && $this->points == 1 ? !empty($this->userId) ? $data['points'] = -(UserDB::find($this->userId)->points) : '' : '';
                //促銷折扣
                !empty($this->promotion_code) ? $data['discount'] = -round($this->discount($data['productAmount'])) : $data['discount'] = 0;
                //總金額
                $data['totalAmount'] = $data['productAmount'] + $data['shippingFee'] + $data['parcelTax'] + $data['discount'];
                if($data['totalAmount'] <= (-$data['points'])){
                    $data['points'] = -$data['totalAmount'];
                    $data['totalAmount'] = 0;
                }else{
                    $data['totalAmount'] = $data['points'] + $data['totalAmount'];
                }
            }
        }
        return $data;
    }

    private function discount($amount = 0)
    {
        $discount = 0;
        $nowtime=intval(date("YmdHis"));
        if($this->promotion_code==="jznxstt" && $nowtime<=20200921000000){ //10%購物車只限舊振南
            $discount = round($amount * 0.10);
        }elseif(strtoupper($this->promotion_code)==="VISA" && $nowtime<=20211231235959 && $nowtime>=20201221000000){
            //商品金額 5% 現折（不折抵關税、運費、其他所有費用），折抵上限 NT$ 150
            if($this->from_country_id == 1 && $this->to_country_id != 1){
                $discount = round($amount * 0.05);
                $discount >= 150 ? $discount = 150 : '';
            }
        }elseif(strtoupper($this->promotion_code)==="TWPAY" && $nowtime<=20211031235959 && $nowtime>=20210601000000){
            //2021/6/1 00:00:00 ~ 2021/10/31 223:59:59
            //商品金額 5% 現折（不折抵關税、運費、其他所有費用），折抵上限 NT$ 300
            $discount = round($amount * 0.05);
            $discount >= 300 ? $discount = 300 : '';
        }
        return $discount;
    }

    private function updateToUser()
    {
        if (!empty($this->userId)) {
            $shoppingCarts = ShoppingCartDB::where([['session',$this->session],['domain',$this->domain]])->get();
            foreach ($shoppingCarts as $shoppingCart) {
                if (empty($shoppingCart->user_id)) {
                    $shoppingCart->update(['user_id' => $this->userId]);
                }
            }
        }
    }

    private function removeShoppingCart($remove)
    {
        $shoppingCarts = ShoppingCartDB::join('product_model','product_model.id','shopping_cart.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->where([['session',$this->session],['domain',$this->domain]]);
        !empty($this->userId) ? $shoppingCarts = $shoppingCarts->orWhere([['user_id',$this->userId],['domain',$this->domain]]) : '';
        $shoppingCarts = $shoppingCarts->select(['shopping_cart.*','product.category_id'])->get();
        foreach ($shoppingCarts as $shoppingCart) {
            if(($remove == 'ticket' && $shoppingCart->category_id == 17) || ($remove == 'nonTicket' && $shoppingCart->category_id != 17)){
                $shoppingCart->delete();
            }
        }
    }
}
