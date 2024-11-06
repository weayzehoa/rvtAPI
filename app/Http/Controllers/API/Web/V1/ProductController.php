<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryReceiverBaseSetting as ReceiverBaseSettingDB;
use App\Models\iCarryHotProduct as HotProductDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\GateSystemSetting as SystemSettingDB;
use App\Models\iCarryUserFavorite as UserFavoriteDB;
use DB;
use Carbon\Carbon;
use Validator;
use Illuminate\Validation\Rule;

use App\Traits\ProductAvailableDate;
use App\Traits\ProductStockDays;

class ProductController extends Controller
{
    use ProductAvailableDate, ProductStockDays;

    public function __construct()
    {
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $this->request = request();
        $request = $this->request;
        $this->rules = [
            'keyword' => 'nullable|string',
            'from_country_id' => 'nullable|numeric|min:1',
            'to_country_id' => 'required_with:from_country_id|numeric|min:1',
            'shippingMethod' => 'required_with:from_country_id,to_country_id|numeric|min:1',
            'pickupDate' => [Rule::requiredIf(function () use ($request) {
                return ($request->from_country_id == 1 && $request->to_country_id == 1);
            }),'date'],
            'categoryIds'   => 'nullable|array',
            'categoryIds.*' => 'integer',
            'lang' => 'nullable|in:en,jp,kr,th',
            'limit' => 'nullable|numeric|min:0',
            'priceMin' => 'nullable|numeric|min:0',
            'priceMax' => 'nullable|numeric|max:4000',
            'sort' => 'nullable|in:priceHighToLow,priceLowToHigh,latest,hotest',
        ];
        $this->showRules = [
            'to_country_id' => 'nullable|numeric|min:1',
            'shippingMethod' => 'required_with:to_country_id|numeric|min:1',
            'pickupDate' => 'required_if:to_country_id,1|date|regex:/^[+0-9-]+$/',
            'lang' => 'nullable|in:en,jp,kr,th',
        ];
    }

    public function index()
    {
        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->rules)->errors(), 400);
        }
        $lang = '';
        $today = date('Y-m-d');
        $fileHost = env('AWS_FILE_URL');
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $$key = $value;
            }
        }
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $hotProductTable = env('DB_ICARRY').'.'.(new HotProductDB)->getTable();
        $userFavoriteTable = env('DB_ICARRY').'.'.(new UserFavoriteDB)->getTable();

        $products = ProductDB::with('styles','packs','userFavorites')->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->where($vendorTable.'.is_on',1)
        ->whereIn($productTable.'.status',[1,-3])
        ->where($productTable.'.is_del',0);
        isset($pickupDate) ? $stockDays = $this->productStockDays($today,$pickupDate) : '';
        isset($priceMin) ? $products = $products->where($productTable.'.price','>=',$priceMin) : '';
        isset($priceMax) ? $products = $products->where($productTable.'.price','<=',$priceMax) : '';
        isset($categoryIds) ? $products = $products->whereIn($productTable.'.category_id',$categoryIds) : '';
        if(isset($from_country_id)){
            $products = $products->where($productTable.'.from_country_id',$from_country_id);
            if($from_country_id == 1 && $from_country_id == $to_country_id){
                if ($shippingMethod == 1) {
                    $products = $products->where($productTable.'.airport_days', '<=', $stockDays);
                } elseif ($shippingMethod == 2) {
                    $products = $products->where($productTable.'.hotel_days', '<=', $stockDays);
                }else{
                    $shippingMethod = 6;
                    $products = $products->where($productTable.'.hotel_days', '<=', $stockDays);
                }
            }else{
                $from_country_id != 1 && $to_country_id == 1 ? $shippingMethod = 5 : $shippingMethod = 4;
            }
        }
        if(isset($keyword)){
            $products = $products->where(function ($query) use ($keyword,$productTable,$vendorTable) {
                $query->where($productTable.'.tags','like',"%$keyword%")
                    ->orWhere($productTable.'.name','like',"%$keyword%")
                    ->orWhere($vendorTable.'.name','like',"%$keyword%")
                    ->orWhereIn($productTable.'.id',ProductLangDB::where('name','like',"%$keyword%")->select('product_id')->groupBy('product_id'))
                    ->orWhereIn($vendorTable.'.id',VendorLangDB::where('name','like',"%$keyword%")->select('vendor_id')->groupBy('vendor_id'));
            });
        }
        $products = $products->select([
            $productTable.'.id',
            $productTable.'.name as name',
            $productTable.'.model_type',
            $vendorTable.'.id as vendor_id',
            $vendorTable.'.name as vendor_name',
            $productTable.'.allow_country',
            $productTable.'.price',
            $productTable.'.fake_price',
            $productTable.'.status',
            $productTable.'.pass_time',
            'hotest' => HotProductDB::whereColumn($hotProductTable.'.product_id',$productTable.'.id')->select([
                DB::raw("(CASE WHEN $hotProductTable.vendor_id = 482 THEN FLOOR( 444 + RAND() * 2345) ELSE $hotProductTable.hits END) as hotest")
            ])->limit(1),
            DB::raw("(CASE WHEN $productTable.new_photo1 is not null THEN CONCAT('$fileHost',$productTable.new_photo1) WHEN $productTable.photo1 is not null THEN $productTable.photo1 ELSE null END) as image1"),
            DB::raw("(CASE WHEN $productTable.new_photo2 is not null THEN CONCAT('$fileHost',$productTable.new_photo2) WHEN $productTable.photo2 is not null THEN $productTable.photo2 ELSE null END) as image2"),
            DB::raw("(CASE WHEN $productTable.new_photo3 is not null THEN CONCAT('$fileHost',$productTable.new_photo3) WHEN $productTable.photo3 is not null THEN $productTable.photo3 ELSE null END) as image3"),
            DB::raw("(CASE WHEN $productTable.new_photo4 is not null THEN CONCAT('$fileHost',$productTable.new_photo4) WHEN $productTable.photo4 is not null THEN $productTable.photo4 ELSE null END) as image4"),
            DB::raw("(CASE WHEN $productTable.new_photo5 is not null THEN CONCAT('$fileHost',$productTable.new_photo5) ELSE null END) as image5"),
        ]);
        if(isset($from_country_id)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN FIND_IN_SET('$shippingMethod',$productTable.shipping_methods) THEN (CASE WHEN FIND_IN_SET('$to_country_id',$productTable.allow_country_ids) THEN 0 WHEN $productTable.allow_country_ids = '' THEN 0 WHEN $productTable.allow_country_ids is null THEN 0 ELSE 1 END) ELSE 1 END) as canNotSend"),
            ]);
        }
        if(!empty($lang) && in_array($lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '$lang' limit 1) ELSE $productTable.name END) as name"),
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '$lang' limit 1) ELSE $vendorTable.name END) as vendor_name"),
            ]);
        }
        //排序
        if(isset($sort)){
            if($sort == 'priceHighToLow'){
                $products = $products->orderBy($productTable.'.price','desc');
            }elseif($sort == 'priceLowToHigh'){
                $products = $products->orderBy($productTable.'.price','asc');
            }elseif($sort == 'latest'){
                $products = $products->orderBy($productTable.'.pass_time','desc');
            }elseif($sort == 'hotest'){
                $products = $products->orderBy('hotest','desc');
            }
        }else{
            $products = $products->orderBy('hotest','desc');
        }
        //限制筆數
        isset($limit) ? $products = $products->limit($limit) :'';

        $products = $products->get();

        //檢查是否支援寄送或庫存不足
        foreach($products as $product){
            $product->outOffStock = 0;
            //庫存不足
            if(!empty($product->styles) && count($product->styles) > 0){
                $tmp = $product->styles;
            }elseif(!empty($product->packs) && count($product->packs) > 0){
                $tmp = $product->packs;
            }
            if(!empty($tmp) && count($tmp) > 0){
                foreach($tmp as $t){
                    if($t->quantity <= 0){
                        $product->outOffStock++;
                    }
                }
                if($product->outOffStock == count($tmp)){
                    $product->outOffStock = 1;
                }
            }
            $product->status == -3 ? $product->outOffStock = 1 : '';
            if(!empty($this->userId)){
                $product->is_favorite = 0;
                if(count($product->userFavorites) > 0){
                    foreach($product->userFavorites as $user){
                         if($user->user_id == $this->userId){
                            if($product->id == $user->table_id){
                                $product->is_favorite = 1;
                                break;
                            }
                         }
                    }
                }
            }
            //清除不需要的變數
            unset($product->userFavorites);
            unset($product->model_type);
            unset($product->allow_country);
            unset($product->hotest);
            unset($product->status);
            unset($product->packs);
            unset($product->styles);
        }
        return $this->successResponse($products->count(), $products);
    }

    public function show($id)
    {
        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->showRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->showRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->showRules))){
                $this->{$key} = $$key = $value;
            }
        }
        $fileHost = env('AWS_FILE_URL');
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $hotProductTable = env('DB_ICARRY').'.'.(new HotProductDB)->getTable();
        $userFavoriteTable = env('DB_ICARRY').'.'.(new UserFavoriteDB)->getTable();

        //單一款式 2637, 多款式 12674, 組合商品 12307
        $grossWeightRate = SystemSettingDB::first()->gross_weight_rate;

        $product = ProductDB::with('styles','packages','userFavorites','vendorHotProducts')
                    ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                    ->join($productModelTable,$productModelTable.'.product_id',$productTable.'.id')
                    ->where($productTable.'.is_del',0)
                    ->where($vendorTable.'.is_on',1)
                    ->whereIn($productTable.'.status',[1,-3])
                    ->select([
                        $productTable.'.id',
                        $productTable.'.brand',
                        $productTable.'.fake_price',
                        $productTable.'.price',
                        $productTable.'.name',
                        $productTable.'.title',
                        $productTable.'.intro',
                        $productTable.'.storage_life',
                        $productTable.'.net_weight',
                        DB::raw("($productTable.gross_weight * $grossWeightRate ) as gross_weight"),
                        $productTable.'.serving_size',
                        $productTable.'.model_type',
                        $productTable.'.model_name',
                        $productTable.'.unable_buy',
                        $productTable.'.status',
                        $productTable.'.vendor_earliest_delivery_date',
                        $productTable.'.airplane_days',
                        $productTable.'.hotel_days',
                        DB::raw("(CASE WHEN $productTable.model_type = 1 THEN $productModelTable.id ELSE null END) as product_model_id"),
                        DB::raw("(CASE WHEN $productTable.model_type = 1 THEN $productModelTable.sku ELSE null END) as sku"),
                        DB::raw("(CASE WHEN $productTable.model_type = 1 THEN $productModelTable.quantity ELSE null END) as quantity"),
                        DB::raw("(CASE WHEN $productTable.model_type = 1 and $productModelTable.quantity <= 0 THEN 1 ELSE 0 END) as outOffStock"),
                        $vendorTable.'.id as vendor_id',
                        $vendorTable.'.name as vendor_name',
                        $vendorTable.'.summary as vendor_summary',
                        $vendorTable.'.description as vendor_description',
                        $productTable.'.specification',
                        'hotest' => HotProductDB::whereColumn($hotProductTable.'.product_id',$productTable.'.id')->select([
                            DB::raw("(CASE WHEN $hotProductTable.vendor_id = 482 THEN FLOOR( 444 + RAND() * 2345) ELSE $hotProductTable.hits END) as hotest")
                        ])->limit(1),
                        DB::raw("(CASE WHEN $productTable.new_photo1 is not null THEN CONCAT('$fileHost',$productTable.new_photo1) WHEN $productTable.photo1 is not null THEN $productTable.photo1 ELSE null END) as image1"),
                        DB::raw("(CASE WHEN $productTable.new_photo2 is not null THEN CONCAT('$fileHost',$productTable.new_photo2) WHEN $productTable.photo2 is not null THEN $productTable.photo2 ELSE null END) as image2"),
                        DB::raw("(CASE WHEN $productTable.new_photo3 is not null THEN CONCAT('$fileHost',$productTable.new_photo3) WHEN $productTable.photo3 is not null THEN $productTable.photo3 ELSE null END) as image3"),
                        DB::raw("(CASE WHEN $productTable.new_photo4 is not null THEN CONCAT('$fileHost',$productTable.new_photo4) WHEN $productTable.photo4 is not null THEN $productTable.photo4 ELSE null END) as image4"),
                        DB::raw("(CASE WHEN $productTable.new_photo5 is not null THEN CONCAT('$fileHost',$productTable.new_photo5) ELSE null END) as image5"),
                    ]);
        if(!empty($this->to_country_id)){
            //寄送國家為台灣時shippingMethod改為6(寄送當地)
            $this->to_country_id == 1 && $this->shippingMethod == 4 ? $this->shippingMethod = 6 : '';
            $this->to_country_id != 1 ? $this->shippingMethod == 4 : ''; //寄送國家非台灣shippingMethod
            $product = $product->addSelect([
                DB::raw("(CASE WHEN FIND_IN_SET('$shippingMethod',$productTable.shipping_methods) THEN (CASE WHEN FIND_IN_SET('$to_country_id',$productTable.allow_country_ids) THEN 0 WHEN $productTable.allow_country_ids = '' THEN 0 WHEN $productTable.allow_country_ids is null THEN 0 ELSE 1 END) ELSE 1 END) as canNotSend"),
            ]);
        }

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $product = $product->addSelect([
                DB::raw("(CASE WHEN (SELECT brand from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT brand from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT brand from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT brand from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.brand END) as brand"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.name END) as name"),
                DB::raw("(CASE WHEN (SELECT title from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT title from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT title from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT title from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.title END) as title"),
                DB::raw("(CASE WHEN (SELECT intro from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT intro from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT intro from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT intro from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.intro END) as intro"),
                DB::raw("(CASE WHEN (SELECT serving_size from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT serving_size from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT serving_size from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT serving_size from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.serving_size END) as serving_size"),
                DB::raw("(CASE WHEN (SELECT specification from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT specification from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT specification from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT specification from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.specification END) as specification"),
                DB::raw("(CASE WHEN (SELECT unable_buy from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT unable_buy from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT unable_buy from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT unable_buy from product_langs where product_langs.product_id = $productTable.id and product_langs.lang = 'en' limit 1) ELSE $productTable.unable_buy END) as unable_buy"),
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) ELSE $vendorTable.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT summary from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT summary from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT summary from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT summary from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) ELSE $vendorTable.summary END) as vendor_summary"),
                DB::raw("(CASE WHEN (SELECT description from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT description from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT description from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT description from vendor_langs where vendor_langs.vendor_id = $vendorTable.id and vendor_langs.lang = 'en' limit 1) ELSE $vendorTable.description END) as vendor_description"),
            ]);
        }

        $product = $product->find($id);

        if(!empty($product)){
            $temp = [];
            if($product->model_type != 1){
                unset($product->product_model_id);
                unset($product->sku);
            }
            $product->model_type == 2 ? $product->styles = $temp = $product->styles : '';
            $product->model_type == 3 ? $product->packages = $temp = $product->packages : '';
            if(count($temp) > 0){
                foreach ($temp as $tmp) {
                    $tmp->outOffStock = 0;
                    $tmp->quantity <= 0 ? $tmp->outOffStock++ : '';
                    $tmp->outOffStock > 0 ? $tmp->outOffStock = 1 : '';
                    unset($tmp->id);
                    unset($tmp->product_id);
                    // unset($tmp->quantity);
                    unset($tmp->safe_quantity);
                }
            }
            $product->status == -3 ? $product->outOffStock = 1 : '';

            if(!empty($this->pickupDate)){
                $product->canNotPickup = 0;
                $product->take_tiem = $this->pickupDate;
                $product->payTime = date('Ymd');
                $product->min_pay_time = date('Y-m-d');
                $product->min_pay_time = date('Y-m-d');
                !empty($this->shippingMethod) && $this->shippingMethod == 1 ? $product->max_days = $product->airplane_days : $product->max_days = $product->hotel_days;
                !empty($this->shippingMethod) && $this->shippingMethod == 1 ? $product->max_receiver_key_time = $this->pickupDate : $product->max_receiver_key_time = null; //機場提貨時間
                $availableDate = $this->getProductAvailableDate($product);
                strtotime($this->pickupDate) < strtotime($availableDate) ? $product->canNotPickup = 1 : '';
                unset($product->take_tiem);
                unset($product->payTime);
                unset($product->min_pay_time);
                unset($product->min_pay_time);
                unset($product->max_days);
                unset($product->max_receiver_key_time);
            }

            if(!empty($this->userId)){
                $product->is_favorite = 0;
                if(count($product->userFavorites) > 0){
                    foreach($product->userFavorites as $user){
                         if($user->user_id == $this->userId){
                            if($product->id == $user->table_id){
                                $product->is_favorite = 1;
                                break;
                            }
                         }
                    }
                }
            }

            //清除不需要的變數
            unset($product->userFavorites);

            return $this->successResponse(1, $product);
        }
        return $this->appCodeResponse('Error', 0, '商品已下架/不存在', 400);
    }

    public function availableDate($id)
    {
        return $this->appCodeResponse('Error', 9, '此API已失效。', 400);
        $rules = [
            'shippingMethod' => 'required|in:1,2',
        ];
        //驗證失敗返回訊息
        if (Validator::make(request()->all(), $rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $rules)->errors(), 400);
        }
        $shippingMethod = request()->shippingMethod;

        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        //ex: 22578 組合商品 備貨 4 天, 10974 單一商品 備貨10天
        $product = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->where($productTable.'.status',1)
            ->where($productTable.'.is_del',0)
            ->where($productModelTable.'.is_del',0)
            ->select([
                $productTable.'.allow_country_ids',
                $shippingMethod == 1 ? $productTable.'.airplane_days as stockDays' : $productTable.'.hotel_days as stockDays',
            ])->findOrFail($id);

        $allowCountry = explode(',',$product->allow_country_ids);
        if(in_array(1,$allowCountry)){
            $availableDate = $this->productAvailableDate($product->stockDays);
            return response()->json(['availableDate' => $availableDate],200);
        }else{
            return $this->appCodeResponse('Error', 999, '此商品不允許寄送台灣', 400);
        }
    }

    public function allowCountry($id)
    {
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $product = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->where('status',1)
            ->select([
                $productTable.'.allow_country_ids',
            ])->findOrFail($id);
        $countryIds = array_unique(explode(',',$product->allow_country_ids));
        sort($countryIds);
        $allowCountry = join(',',$countryIds);
        return response()->json(['allowCountry' => $allowCountry],200);
    }

}
