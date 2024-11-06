<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as productDB;
use App\Models\iCarryShopcomOrder as ShopcomOrderDB;
use App\Models\iCarryTradevanOrder as TradevanOrderDB;
use App\Models\iCarryOrderItem as OrderItemDB;
use App\Models\iCarryOrderItemPackage as OrderItemPackageDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryShippingVendor as ShippingVendorDB;
use App\Models\iCarrySpgateway as SpgatewayDB;
use App\Models\iCarryShoppingCart as ShoppingCartDB;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\GateSystemSetting as SystemSettingDB;
use App\Models\iCarryOrderAsiamiles as OrderAsiamilesDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryUserAddress as UserAddressDB;
use App\Models\iCarryPayMethod as PayMethodDB;
use App\Models\iCarryDigiwinPayment as DigiwinPaymentDB;
use Validator;
use Illuminate\Validation\Rule;
use App\Traits\ProductAvailableDate;
use App\Traits\ProductAvailableShippingDate;
use App\Traits\BookShippingDate;
use App\Traits\LanguagePack;
use App\Traits\ACPayTrait;
use App\Traits\NewebPayTrait;
use App\Traits\EsunBankTrait;
use App\Traits\ShopcomFunctionTrait;
use App\Traits\TaishinBankTrait;
use DB;
use Curl;

class OrderController extends Controller
{
    use TaishinBankTrait,ShopcomFunctionTrait,EsunBankTrait,ACPayTrait,NewebPayTrait,BookShippingDate, ProductAvailableDate, LanguagePack;

    protected $payProvider = ['ACpay' => 'acpay', '智付通信用卡' => 'spgateways', '智付通ATM' => 'spgateways', '智付通CVS' => 'spgateways', '智付通銀聯卡' => 'spgateways', '玉山信用卡' => 'esun', '玉山行動銀行' => 'esun', '玉山支付寶' => 'esun', '台新銀聯卡' => 'taishin'];
    protected $userId;

    public function __construct()
    {
        $this->vendorTable = $orderTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $this->productTable = $orderTable = env('DB_ICARRY').'.'.(new productDB)->getTable();
        $this->productModelTable = $orderTable = env('DB_ICARRY').'.'.(new productModelDB)->getTable();
        $this->orderTable = $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $this->orderItemTable = env('DB_ICARRY').'.'.(new OrderItemDB)->getTable();
        $this->countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $this->userTable = env('DB_ICARRY').'.'.(new UserDB)->getTable();
        $this->shopcomOrderTable = env('DB_ICARRY').'.'.(new ShopcomOrderDB)->getTable();
        $this->tradevanOrderTable = env('DB_ICARRY').'.'.(new TradevanOrderDB)->getTable();
        $this->aesKey = env('APP_AESENCRYPT_KEY');
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->request = request();
        // if(env('APP_ENV') == 'production'){
            $this->middleware(['api','refresh.token']);
            if(auth('webapi')->check()){
                $this->userId = auth('webapi')->user()->id;
            }elseif(!empty($this->request->icarry_uid)){
                $this->userId = $this->request->icarry_uid;
            }
        // }else{
        //     $this->userId = null;
        //     $this->userId = 84533; //Roger
        //     $this->userId = 4588; //信成
        //     // $this->userId = 103467; //其他user
        // }
        $this->middleware(['api','refresh.token']);
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }elseif(!empty($this->request->icarry_uid)){
            $this->userId = $this->request->icarry_uid;
        }
        $userId = $this->userId;
        $request = $this->request;
        //翻譯及字串取代
        $this->translate = $this->translate(['免自提','超商代碼','ATM轉帳','尚未付款','信用卡','待出貨','集貨中','已出貨','已完成','已取消','機場提貨','旅店提貨','現場提貨','指定地址配送','手機條碼載具','自然人憑證條碼載具','智付寶載具','收據捐贈：慈善基金會','二聯式','三聯式','預計將於寄出後{0}個工作天內寄達。']);
        $this->replaceByReplace = 'pay_method'; //使用replace方式
        $findStr = ['智付通','國際','玉山','台新','資策會','CVS','ATM','信用卡']; //找出字串
        $replaceStr = ['','','','',$this->translate['免自提'],$this->translate['超商代碼'],$this->translate['ATM轉帳'],$this->translate['信用卡']]; //要取代的字串
        for($i=0;$i<count($findStr);$i++){
            $this->replaceByReplace = "REPLACE(".$this->replaceByReplace.",'".$findStr[$i]."','".$replaceStr[$i]."')";
        }
        $this->replaceStatus = '';
        $status = [0 => $this->translate['尚未付款'], 1 => $this->translate['待出貨'], 2 => $this->translate['集貨中'], 3 => $this->translate['已出貨'], 4 => $this->translate['已完成'], -1 => $this->translate['已取消']];
        foreach ($status as $key => $value) {
            $this->replaceStatus .= " WHEN $orderTable.status = $key THEN '$value' ";
        }

        $this->indexRules = [
            'keyword' => 'nullable|string',
            'lang' => 'nullable|string|in:en,jp,kr,th',
        ];

        $this->updateRule = [
            'type' => 'required|in:finished,repay',
            'pay_method' => 'required_if:type,repay',
        ];
        $this->buyAgain = [
            'session' => [Rule::requiredIf(function () use ($userId) {
                return (empty($userId));
            }),'uuid','max:40'],
            'domain' => 'required|string|max:50',
        ];
        $this->storeRules = [
            'domain' => 'required|string|max:50',
            'from_country_id' => 'required|in:1,5|numeric',
            'to_country_id' => 'required|numeric',
            'shipping_method_id' => [Rule::requiredIf(function () use ($request) {
                return ($request->from_country_id == 1 && $request->to_country_id == 1);
            }),'numeric','in:1,2,3,4,5,6'],
            'promotion_code'  => 'nullable|in:jznxstt,VISA,TWPAY',
            'points' => 'nullable|numeric|in:1,0',
            'pay_method' => 'required_without:partner_id|string|max:20',
            'create_type' => 'required_if:domain,icarry.me|string|max:20',
            'buyer_name' => 'required|string|max:20',
            'buyer_email' => 'required|email',
            'invoice_sub_type' => 'required|numeric|in:1,2,3',
            'carrier_type' => 'required_with:carrier_num|in:0,1,2',
            'carrier_num' => [
                'required_with:carrier_type',
                'max:20',
                function ($attribute, $value, $fail) {
                    if($this->request->carrier_type == 0){
                        if(!preg_match("/^\//",$value) || strlen($value) != 8){
                            $fail('手機條碼格式錯誤，第一碼為 / 斜線，共 8 碼。');
                        }
                    }elseif($this->request->carrier_type == 1){
                        if(strlen($this->request->carrier_num) != 16){
                            $fail('自然人憑證條碼格式錯誤，前二碼為大寫字母，共 16 碼。');
                        }
                        for ($i = 0; $i < 2; $i++) {
                            if (ord($value[$i]) >= ord('A') && ord($value[$i]) <= ord('Z')) {
                            }else{
                                $fail('自然人憑證條碼格式錯誤，前二碼為大寫字母，共 16 碼。');
                            }
                        }
                    }
                },
            ],
            'invoice_title' => 'required_if:invoice_sub_type,3|string|max:100',
            'invoice_number' => 'required_if:invoice_sub_type,3|digits:8',
            'asiamiles_account' => 'nullable|digits:10',
            'user_memo' => 'nullable|string|max:255',
            'airport_pickup_location' => [Rule::requiredIf(function () use ($request) {
                                return ($request->shipping_method_id == 1 && $request->to_country_id == 1);
                            }),'string','max:100'],
            'airport_flight_number' => [Rule::requiredIf(function () use ($request) {
                                return ($request->shipping_method_id == 1 && $request->to_country_id == 1);
                            }),'string','max:20'],
            'take_time' => 'required_if:to_country_id,1|date|max:20',
            'hotel_checkout_date' => [Rule::requiredIf(function () use ($request) {
                                        return ($request->shipping_method_id == 2 && $request->to_country_id == 1);
                                    }),
                                    'date',
                                    function ($attribute, $value, $fail) {
                                        if(strtotime( $this->request->take_time ) > strtotime( $value )){
                                            $fail(str_replace('_',' ',$attribute).' 需晚於預定提貨日。');
                                        }
                                    },
                                    'max:20'],
            'hotel_name' =>  [Rule::requiredIf(function () use ($request) {
                    return ($request->shipping_method_id == 2 && $request->to_country_id == 1);
                }),'string','max:100'],
            'hotel_room_number' => 'nullable|string|max:20',
            'hotel_address' =>  [Rule::requiredIf(function () use ($request) {
                    return ($request->shipping_method_id == 2 && $request->to_country_id == 1);
                }),'string','max:255'],
            'receiver_name' => [Rule::requiredIf(function () use ($request) {
                                if($request->to_country_id == 1){
                                    if($request->shipping_method_id == 1 || $request->shipping_method_id == 2){
                                        return true;
                                    }
                                }
                            }),'string','max:40'],
            'receiver_email' => [Rule::requiredIf(function () use ($request) {
                                if($request->to_country_id == 1){
                                    if($request->shipping_method_id == 1 || $request->shipping_method_id == 2){
                                        return true;
                                    }
                                }
                            }),'email','max:255'],
            'receiver_nation_number' => [Rule::requiredIf(function () use ($request) {
                                            if($request->to_country_id == 1){
                                                if($request->shipping_method_id == 1 || $request->shipping_method_id == 2){
                                                    return true;
                                                }
                                            }
                                        }),'regex:/^[+o0-9]+$/',
                                        'max:5'],
            'receiver_phone_number' => [Rule::requiredIf(function () use ($request) {
                                if($request->to_country_id == 1){
                                    if($request->shipping_method_id == 1 || $request->shipping_method_id == 2){
                                        return true;
                                    }
                                }
                            }),'numeric'],
            'receiver_other_contact' => 'nullable|string|max:10',
            'receiver_other_contact_value' => 'nullable|string|max:100',
            'user_address_id' => 'required_if:shipping_method_id,4|numeric',
            'agree' => 'required_if:to_country_id,2|in:1',
            'exchange_rate' => 'nullable|integer',
            'returnURL' => 'required|url|max:200',
        ];
        $this->lang = request()->lang;
        $systemSetting = SystemSettingDB::first();
        $this->grossWeightRate = $systemSetting->gross_weight_rate;

    }
    //訂單索引
    public function index()
    {
        if (Validator::make($this->request->all(), $this->indexRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->indexRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->indexRules))){
                $this->{$key} = $$key = $value;
            }
        }

        $orders = OrderDB::where($this->orderTable.'.user_id',$this->userId);
        $orders = $orders->where($this->orderTable.'.create_time','>=','2020-01-01 00:00:00.000'); //限制訂單2020-01-01之後

        if(!empty($this->keyword)){
            $orders = $orders->where(function ($query) {
                $orderIds = OrderDB::where($this->orderTable.'.user_id',$this->userId)->select($this->orderTable.'.id');
                $query->where($this->orderTable.'.order_number','like',"%$this->keyword%")
                ->orWhere($this->orderTable.'.receiver_name','like',"%$this->keyword%")
                ->orWhereIn($this->orderTable.'.id',OrderItemDB::whereIn($this->orderItemTable.'.order_id',$orderIds)->where($this->orderItemTable.'.product_name','like',"%$this->keyword%")->select($this->orderItemTable.'.order_id'));
            });
        }

        $orders = $orders->select([
            $this->orderTable.'.id',
            $this->orderTable.'.order_number',
            // DB::raw("(CASE WHEN NOW() >= DATE_ADD(create_time, INTERVAL 6 HOUR) THEN 0 ELSE 1 END) as in_six_hour"),
            DB::raw("(DATE_FORMAT($this->orderTable.create_time,'%Y-%m-%d')) as create_date"),
            DB::raw("($this->orderTable.amount - $this->orderTable.spend_point - $this->orderTable.discount + $this->orderTable.shipping_fee + $this->orderTable.parcel_tax) as price"),
            // DB::raw("($this->replaceByReplace) as pay_method"),
            DB::raw("(CASE $this->replaceStatus END) as order_status"),
            $this->orderTable.'.status',
            // 'to as to_country_id',
            'to_country_id' => CountryDB::whereColumn($this->orderTable.'.ship_to',$this->countryTable.'.name')->select($this->countryTable.'.id')->limit(1),
            $this->orderTable.'.ship_to',
            $this->orderTable.'.receiver_name',
            $this->orderTable.'.receiver_address',
            DB::raw("(SELECT count($this->orderItemTable.id) from $this->orderItemTable where $this->orderTable.id = $this->orderItemTable.order_id) as totalItems ")
        ]);
        if(!empty($lang) && in_array($lang,$this->langs)){
            $orders = $orders->addSelect([
                DB::raw("(CASE WHEN (SELECT $this->countryTable.name_$lang from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1) is not null THEN (SELECT name_$lang from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1) ELSE (CASE WHEN (SELECT name_en from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1) is not null THEN (SELECT name_en from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1) ELSE (SELECT name from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1) END) END) as ship_to"),
            ]);
        }
        $orders = $orders->orderBy('create_time','desc');
        !empty($this->keyword) ? $orders = $orders->distinct()->limit(100)->get() : $orders = $orders->limit(100)->get();
        return $this->successResponse($orders->count(),$orders);
    }
    //建立訂單
    public function store(Request $request)
    {
        if (Validator::make($this->request->all(), $this->storeRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->storeRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->storeRules))){
                $this->{$key} = $value;
            }
        }
        //寄送中國必須要同意規定
        if($this->to_country_id == 2){
            if(empty($this->agree) || $this->agree != 1){
                return $this->appCodeResponse('Error', 1, '未勾選同意中國政策規定。', 400);
            }
        }
        //資料整理
        $collectData = $this->collectData();
        if($collectData == 'userAddressFail'){
            return $this->appCodeResponse('Error', 2, '使用者常用地址id錯誤', 400);
        }elseif($collectData == 'payMethodFail'){
            return $this->appCodeResponse('Error', 3, '付款方式錯誤', 400);
        }elseif($collectData == 'createTypeFail'){
            return $this->appCodeResponse('Error', 4, 'createType錯誤', 400);
        }

        //找可結帳購物車及計算資料
        $available = $this->findAvailableCart($collectData);
        // return $this->debugResponse($available);

        if(count($available) > 0){
            //計算資料
            $calculation = $this->calculation($available);
            if(!is_array($calculation) && $caculation == 'shippingFeeFail'){
                $from = CountryDB::find($this->from_country_id)->name;
                $to = CountryDB::find($this->to_country_id)->name;
                return $this->appCodeResponse('Error', 5, "不提供 $from 發貨到 $to", 400);
            }
            //合併資料
            $orderData = array_merge($collectData,$calculation);

            //移除無用的參數
            unset($orderData['from']);
            unset($orderData['to']);
            //建立訂單
            $order = OrderDB::create($orderData);
            //取出資料給其他function用
            $this->orderId = $order->id;
            $this->orderNumber = $order->order_number;
            $this->orderStatus = $order->status;
            $this->totalAmount = $order->amount + $order->parcel_tax + $order->shipping_fee - $order->discount - $order->spend_point;
            $this->buyerName = $order->buyer_name;
            $this->buyerEmail = $order->buyer_email;
            $this->description = 'iCarry我來寄 訂單';
            $order->returnURL = $this->returnURL;

            //商品處理
            $this->moveShoppingCartToOrderItem($available);

            // 扣除庫存處理
            $this->stocks($available);

            // 亞洲萬里通處理
            $this->asiamiles($this->totalAmount);

            // 購物金處理
            $pointType = "訂單 {$order->order_number} 使用購物金 {$order->spend_point} 點";
            $this->points($order,$pointType);

            //付款處理(合作廠商不檢查pay_method)
            if(!empty($this->pay_method)){
                if ($order->pay_method != '購物金') {
                    if($this->pay_method == 'ACpay'){
                        $payUrl = $this->ACPay($this->orderNumber,$this->totalAmount,$this->buyerEmail,$this->returnURL);
                        return $this->appDataResponse('Success', 0, '建立訂單成功。', ['order_id' => $this->orderId, 'pay_url' => $payUrl], 200);
                    }else{
                        $result = $this->pay($order);
                        //使用 form 表單 或 url 觸發轉向 (只能放在這邊，不能另外拉出function)
                        if(!empty($result)){
                            if($result['type'] == 'form'){
                                return redirect()->route('pay.index', ['pay' => $result['data']]);
                            }elseif($result['type'] == 'url' && !empty($result['message'])){
                                return $this->appCodeResponse('Error', 99, '台新銀聯卡付款失敗。', 400); //付款失敗
                            }elseif($result['type'] == 'url'){
                                return redirect()->to($result['data']);
                            }else{
                                return null;
                            }
                        }
                    }
                }
            }
            return $this->appDataResponse('Success', 0, '建立訂單成功。', ['order_id' => $this->orderId], 200);
        }else{
            return $this->appCodeResponse('Error', 0, '建立訂單失敗，沒有可結帳商品。', 400);
        }
    }
    //顯示訂單
    public function show($id)
    {
        //id = 119507 order_number = 21040302133794 (ATM轉帳) (VACC)
        //id = 9636 order_number = 17051816523758 (超商代碼) (CVS)
        //id = 70025 機場提貨
        //id = 117330 旅店提貨
        //id = 4513 現場提貨
        //id = 118985 寄送海外, status = 3
        $sm1 = $this->translate['機場提貨'];
        $sm2 = $this->translate['旅店提貨'];
        $sm3 = $this->translate['現場提貨'];
        $sm4 = $this->translate['指定地址配送'];
        $ct0 = $this->translate['手機條碼載具'];
        $ct1 = $this->translate['自然人憑證條碼載具'];
        $ct2 = $this->translate['智付寶載具'];
        $ist1 = $this->translate['收據捐贈：慈善基金會'];
        $ist2 = $this->translate['二聯式'];
        $ist3 = $this->translate['三聯式'];
        $order = OrderDB::with('orderItems')->where('user_id',$this->userId);
        $to = "( SELECT id from $this->countryTable where $this->countryTable.name = $this->orderTable.ship_to limit 1 )";
        $order = $order->where($this->orderTable.'.create_time','>=','2020-01-01 00:00:00.000'); //限制訂單2020-01-01之後

        $order = $order->select([
                'id',
                'user_id',
                'order_number',
                DB::raw("(CASE $this->replaceStatus END) as order_status"),
                'status',
                'from' => CountryDB::whereColumn($this->countryTable.'.name',$this->orderTable.'.origin_country')->select('id')->limit(1),
                'to' => CountryDB::whereColumn($this->countryTable.'.name',$this->orderTable.'.ship_to')->select('id')->limit(1),
                DB::raw("(CASE WHEN $to IN(1,11,12,13) THEN '2-3' WHEN $to IN(3,4,6,7) THEN '2-5' ELSE '2-7' END) as estimate_ship_dates"),
                DB::raw("(CASE WHEN shipping_method = 1 THEN '$sm1' WHEN shipping_method = 2 THEN '$sm2' WHEN shipping_method = 3 THEN '$sm3' WHEN shipping_method IN(4,5,6) THEN '$sm4' ELSE null END) as shipping_method_text"),
                'origin_country',
                'shipping_method',
                'book_shipping_date',
                'receiver_keyword',
                DB::raw("(CASE WHEN shipping_method = 1 THEN DATE_FORMAT(receiver_key_time,'%Y-%m-%d %H:%i') WHEN shipping_method = 2 THEN DATE_FORMAT(receiver_key_time,'%Y-%m-%d %H:%i') WHEN shipping_method = 6 THEN DATE_FORMAT(receiver_key_time,'%Y-%m-%d %H:%i') ELSE null END) as receiver_key_time"),
                DB::raw("($this->replaceByReplace) as pay_method"),
                'receiver_name',
                DB::raw("IF($this->orderTable.receiver_tel IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_tel,'$this->aesKey')) as receiver_tel"),
                'receiver_nation_number',
                DB::raw("IF($this->orderTable.receiver_phone_number IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_phone_number,'$this->aesKey')) as receiver_phone_number"),
                DB::raw("(CASE WHEN (IF($this->orderTable.receiver_tel IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_tel,'$this->aesKey'))) is not null or (IF($this->orderTable.receiver_tel IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_tel,'$this->aesKey'))) != '' THEN (IF($this->orderTable.receiver_tel IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_tel,'$this->aesKey'))) ELSE CONCAT($this->orderTable.receiver_nation_number,(IF($this->orderTable.receiver_phone_number IS NULL,NULL,AES_DECRYPT($this->orderTable.receiver_phone_number,'$this->aesKey')))) END) as receiver_phone"),
                'receiver_email',
                'receiver_address',
                'user_memo',
                'invoice_type',
                DB::raw("(CASE WHEN invoice_sub_type = 1 THEN '$ist1' WHEN invoice_sub_type = 2 THEN '$ist2' WHEN invoice_sub_type = 3 THEN '$ist3' END) as invoice_sub_type"),
                DB::raw("(CASE WHEN carrier_type = '0' THEN '$ct0' WHEN carrier_type = '1' THEN '$ct1' WHEN carrier_type = '2' THEN '$ct2' ELSE null END) as carrier_type"),
                'carrier_num',
                'is_invoice_no',
                'invoice_title',
                'invoice_time',
                'amount',
                'spend_point',
                'shipping_fee',
                'parcel_tax',
                'discount',
                DB::raw("(CASE WHEN `origin_country` = `ship_to` and shipping_method = 1 THEN '當地機場' WHEN `origin_country` = `ship_to` and shipping_method = 2 THEN '當地旅店' WHEN `origin_country` = `ship_to` and shipping_method != 3 THEN '當地地址' ELSE ship_to END) as shipping_to"),
                DB::raw("(amount - spend_point - discount + shipping_fee + parcel_tax) as price"),
                'shippingType' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('shipping_type')->limit(1),
                'freeShipping' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('free_shipping')->limit(1),
                'shippingPrice' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('price')->limit(1),
                'shippingTaxRate' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('tax_rate')->limit(1),
                'shippingDescription' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('description_tw')->limit(1),
                DB::raw("(DATE_FORMAT(create_time,'%Y-%m-%d')) as create_date"),
            ]);
        if(!empty($this->lang)){
            $order = $order->addSelect([
                'shippingDescription' => ShippingFeeDB::whereColumn([['shipping_set.product_sold_country','origin_country'],['shipping_set.shipping_methods','shipping_to']])->select('description_en')->limit(1),
            ]);
        }
        $order = $order->findOrFail($id);
        $pad = null;
        if(strlen($order->receiver_tel)-6 > 7){
            for($i=0;$i<(strlen($order->receiver_tel)-6);$i++){
                $pad .= '*';
            }
            $order->receiver_tel = mb_substr($order->receiver_tel,0,3).$pad.mb_substr($order->receiver_tel,-3);
        }else{
            !empty($order->receiver_tel) ? $order->receiver_tel = mb_substr($order->receiver_tel,0,2).$pad.mb_substr($order->receiver_tel,-2) : '';
        }
        if(strlen($order->receiver_phone)-6 > 7){
            for($i=0;$i<(strlen($order->receiver_phone)-6);$i++){
                $pad .= '*';
            }
            $order->receiver_phone = mb_substr($order->receiver_phone,0,3).$pad.mb_substr($order->receiver_phone,-3);
        }else{
            !empty($order->receiver_phone) ? $order->receiver_phone = mb_substr($order->receiver_phone,0,2).$pad.mb_substr($order->receiver_phone,-2) : '';
        }
        if(strlen($order->receiver_phone_number)-6 > 7){
            for($i=0;$i<(strlen($order->receiver_phone_number)-6);$i++){
                $pad .= '*';
            }
            $order->receiver_phone_number = mb_substr($order->receiver_phone_number,0,3).$pad.mb_substr($order->receiver_phone_number,-3);
        }else{
            !empty($order->receiver_phone_number) ? $order->receiver_phone_number = mb_substr($order->receiver_phone_number,0,2).$pad.mb_substr($order->receiver_phone_number,-2) : '';
        }
        if($order->pay_method == $this->translate['超商代碼'] || $order->pay_method == $this->translate['ATM轉帳']){
            $spResult =  json_decode(json_decode($order->spgateway->get_json)->Result,true);
            $order->pay_expire_time = $spResult["ExpireDate"].' '.$spResult["ExpireTime"];
            if($order->pay_method == $this->translate['ATM轉帳']){
                !empty($spResult["BankCode"]) ? $order->pay_bank_code = $this->bankCode($spResult["BankCode"]) : $order->pay_bank_code = null;
            }
        }
        $order->shippingDescription = str_replace('{0}',$order->estimate_ship_dates,$order->shippingDescription);
        if($order->status == 3 || $order->status == 4){
            $order->order_shippings = $order->orderShippings;
            foreach($order->order_shippings as $shipping){
                $shipping->check_url = ShippingVendorDB::where('name',$shipping->express_way)->first()->api_url.$shipping->express_no;
                unset($shipping->id);
                unset($shipping->order_id);
            }
        }


        $maxStockDays = 0; //訂單中所有商品裡最大備貨日期
        $order->gross_weight = 0; //訂單物品重量

        if(!empty($order->orderItems) && count ($order->orderItems) > 0){
            foreach($order->orderItems as $item){
                $order->gross_weight += ($item->gross_weight / 1000);
                $item->airplane_days > $maxStockDays ? $maxStockDays = $item->airplane_days : '';
                $item->image ? $item->image = $this->awsFileUrl.$item->image : '';
                unset($item->gross_weight);
                unset($item->airplane_days);
                unset($item->order_id);
            }
            $order->gross_weight = round($order->gross_weight,2);
            //預計出貨日, 以訂單建立時間及商品最大備貨天數去計算
            $order->expected_shipping_date = $this->productAvailableDate($maxStockDays,$order->create_date, $type = 'shipping');
            //寄送地為台灣才顯示預計抵達日
            $order->to == 1 ? $order->expected_arrival_date = $this->productAvailableDate($maxStockDays,$order->create_date) : '';
            $order->total_items = count($order->orderItems); //訂單商品數量
        }
        unset($order->spgateway);
        unset($order->shipping_local_id);
        return $this->dataResponse($order,'orders',$id);
    }

    public function update(Request $request, $id)
    {
        return $this->appCodeResponse('Error', 9, '此API已失效。', 400);
        // if (Validator::make($request->all(), $this->updateRule)->fails()) {
        //     return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->updateRule)->errors(), 400);
        // }
        // //將進來的資料作參數轉換(只取rule中有的欄位)
        // foreach ($this->request->all() as $key => $value) {
        //     if(in_array($key, array_keys($this->updateRule))){
        //         $this->{$key} = $$key = $data[$key] = $value;
        //     }
        // }
        // //找出訂單
        // $order = OrderDB::findOrFail($id);
        // //取出資料給其他function用
        // $this->orderId = $this->orderId;
        // $this->orderNumber = $order->order_number;
        // $this->orderStatus = $order->status;
        // $this->totalAmount = $order->amount + $order->parcel_tax + $order->shipping_fee - $order->discount - $order->spend_point;
        // $this->buyerName = $order->buyer_name;
        // $this->buyerEmail = $order->buyer_email;
        // $this->description = 'iCarry我來寄 訂單';
        // //完成訂單
        // if(!empty($request->type) && $request->type == 'finished'){
        //     $order = $order->update(['status' => 4]);
        //     return $this->appCodeResponse('Success',0,'訂單更新完成',200);
        // }
        // //重新付款
        // if (!empty($request->type) && $request->type == 'repay') {
        //     //狀態為0才能重新付款
        //     if($this->orderStatus == 0){
        //         if(!empty($this->pay_method)){
        //             $checkPayMethod = PayMethodDB::where([['is_on',1],['value',$this->pay_method]])->first();
        //             if (!empty($checkPayMethod)) {
        //                 $result = $this->pay($order);
        //                 //使用 form 表單觸發轉向 (只能放在這邊，不能另外拉出function)
        //                 if(!empty($result)){
        //                     if($result['type'] == 'form' && !empty($result['data'])){
        //                         return redirect()->route('pay.index', ['pay' => $result['data']]);
        //                     }
        //                 }
        //             } else {
        //                 return $this->appCodeResponse('Error', 3, '付款方式錯誤/不存在。', 400); //前端給錯value會出現此段
        //             }
        //         }
        //     }
        //     return $this->appCodeResponse('Error', 9, '訂單狀態錯誤，無法重新付款。', 400);
        // }
        // return null;
    }
    //刪除訂單
    public function destroy($id)
    {
        return $order = OrderDB::select([
            'id',
            'order_number',
            'spend_point',
            'amount',
            'points' => UserDB::whereColumn($this->userTable.'.id',$this->orderTable.'.user_id')->select($this->userTable.'.points')->limit(1),
            'shopcom_RID' => ShopcomOrderDB::whereColumn($this->shopcomOrderTable.'.order_id',$this->orderTable.'.id')->select($this->shopcomOrderTable.'.RID')->limit(1),
            'shopcom_Click_ID' => ShopcomOrderDB::whereColumn($this->shopcomOrderTable.'.order_id',$this->orderTable.'.id')->select($this->shopcomOrderTable.'.Click_ID')->limit(1),
            'tradevan_RID' => TradevanOrderDB::whereColumn($this->tradevanOrderTable.'.order_id',$this->orderTable.'.id')->select($this->tradevanOrderTable.'.RID')->limit(1),
            'tradevan_Click_ID' => TradevanOrderDB::whereColumn($this->tradevanOrderTable.'.order_id',$this->orderTable.'.id')->select($this->tradevanOrderTable.'.Click_ID')->limit(1),
            'status',
            'create_time',
        ])->findOrFail($id);
            //訂單狀態0才可以刪除，已付款須由後台管理者取消處理
        if($order->status == 0){
            $orderItems = OrderItemDB::where('order_id',$id)->get();
            // 庫存返回
            if(!empty($orderItems) && count($orderItems) > 0){
                $this->stocks($orderItems,true);
                $orderItems->delete();
            }
            //取消訂單歸還購物金
            if($order->spend_point > 0){
                $pointType = "取消訂單 {$order->order_number} 退回購物金 {$order->spend_point} 點";
                $this->points($order,$pointType,true);
            }
            //取消shopcom訂單
            if($order->shopcom_RID && $order->shopcom_Click_ID){
                $this->cancelSendToShopcom($order->order_number,$order->create_time,$order->amount+$order->parcel_tax,$order->shopcom->RID,$order->shopcom->Click_ID);
            }
            //取消Tradevan訂單
            if($order->tradevan_RID && $order->tradevan_Click_ID){
                $this->cancelSendToTradevan($order->order_number,$order->create_time,$order->amount+$order->parcel_tax,$order->tradevan->RID,$order->tradevan->Click_ID);
            }
            $order->delete();
            return $this->appCodeResponse('Success', 0, '刪除成功。',200);
        }else{
            $order->status == 1 ? $msg = '訂單已付款，無法刪除。' : '';
            $order->status == 2 ? $msg = '訂單集貨中，無法刪除。' : '';
            $order->status == 3 ? $msg = '訂單已出貨，無法刪除。' : '';
            $order->status == 4 ? $msg = '訂單已完成，無法刪除。' : '';
            return $this->appCodeResponse('Error', 0, $msg,400);
        }
    }
    //再買一次
    public function buyAgain($id)
    {
        if (Validator::make($this->request->all(), $this->buyAgain)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->buyAgain)->errors(), 400);
        }
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->buyAgain))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        //找出該筆訂單購買的商品資料
        $orderItems = OrderItemDB::join('product_model','product_model.id','order_item.product_model_id')
            ->join('product','product.id','product_model.product_id')
            ->join('vendor','vendor.id','product.vendor_id')
            ->join('orders','orders.id','order_item.order_id')
            ->where('order_item.order_id',$id)->select([
                'order_item.product_model_id',
                'order_item.quantity',
                'product_model.quantity as product_stock',
                'product.status as product_status',
                'vendor.is_on as vendor_status',
                'vendor.id as vendor_id',
                'orders.shipping_method',
        ])->get();
        $chkStock = 0;
        $count = 0;
        foreach($orderItems as $item){
            if($item->product_status == 1){
                if($item->vendor_status == 1){
                    if($item->quantity < $item->product_stock){
                        $data['user_id'] = $this->userId;
                        $data['product_model_id'] = $item->product_model_id;
                        $data['quantity'] = $item->quantity;
                        $data['shipping_method'] = $item->shipping_method;
                        $data['vendor_id'] = $item->vendor_id;
                        $data['session'] = $this->session;
                        //找是否已存在購物車, 有則數量累加
                        $shoppingCart = ShoppingCartDB::where('product_model_id',$item->product_model_id)
                        ->where(function($query){
                            $query = $query->where('user_id',$this->userId)
                                ->orWhere('session',$this->session);
                        })->first();
                        if(!empty($shoppingCart)){
                            $shoppingCart->update(['quantity' => $shoppingCart->quantity + $item->quantity]);
                        }else{
                            $shoppingCart = ShoppingCartDB::create($data);
                        }
                        $count++;
                    }
                }
            }else{
                $chkStock++;
            }
        }
        if($chkStock > 0){
            if($count > 0){
                return $this->appCodeResponse('Warning', 2, '已重新加入購物車，有部分商品已下架或暫無庫存無法結帳。結帳前請再次檢查購物車。',200);
            }else{
                return $this->appCodeResponse('Warning', 1, '未加入任何商品至購物車，該訂單商品已下架或暫無庫存無法結帳。',200);
            }
        }else{
            return $this->appCodeResponse('Success', 0, '已重新加入購物車。結帳前請再次檢查購物車。',200);
        }
    }

    //銀行代碼
    protected function bankCode($bankCode)
    {
        switch($bankCode){
            case '808':return '玉山銀行( '.$bankCode.' )';break;
            case '812':return '台新銀行( '.$bankCode.' )';break;
            case '008':return '華南銀行( '.$bankCode.' )';break;
            case '007':return '第一銀行( '.$bank_code.' )';break;
            case '017':return '兆豐銀行( '.$bank_code.' )';break;
                default:return '台灣銀行( '.$bankCode.' )';break;
        }
    }

    //找出可結帳購物車
    private function findAvailableCart($collectData)
    {
        $data['available'] = [];
        if($this->shipping_method_id == 4){
            if($this->from_country_id == $this->to_country_id){
                $this->shipping_method_id = 6;
            }elseif($this->to_country_id == 1){
                $this->shipping_method_id = 5;
            }
        }
        $shoppingCarts = ShoppingCartDB::where([['domain',$this->domain],['user_id',$this->userId]])
            ->join('product_model','product_model.id','shopping_cart.product_model_id')
            ->join('product','product.id','product_model.product_id')
            ->join('vendor','vendor.id','product.vendor_id')
            ->join('product_unit_name','product_unit_name.id','product.unit_name_id');
        $shoppingCarts = $shoppingCarts->select([
            'shopping_cart.id',
            'shopping_cart.product_model_id',
            'shopping_cart.quantity',
            'vendor.id as vendor_id',
            'vendor.name as vendor_name',
            'vendor.service_fee as vendor_service_fee_percent',
            'vendor.shipping_verdor_percent',
            'product.id as product_id',
            'product_model.sku',
            'product_model.digiwin_no',
            DB::raw('( CASE WHEN model_type = 3 THEN product_model.name ELSE product.name END ) as product_name'),
            'product_unit_name.name as unit_name',
            'product.price',
            DB::raw("(CASE WHEN product.gross_weight > 0 THEN product.gross_weight * $this->grossWeightRate ELSE 0 END) as gross_weight"),
            'product.net_weight',
            'product.service_fee_percent as product_service_fee_percent',
            'product.hotel_days',
            'product.vendor_earliest_delivery_date',
            'product.direct_shipment',
            'product.is_tax_free',
            'product.vendor_price as purchase_price',
            'product.package_data',
            DB::raw("(CASE WHEN vendor.is_on = 1 AND product.status = 1 AND product_model.quantity > shopping_cart.quantity AND '$this->from_country_id' = product.from_country_id AND find_in_set('$this->to_country_id',product.allow_country_ids) AND find_in_set('$this->shipping_method_id', IF(product.shipping_methods = '' OR product.shipping_methods IS NULL,'1,2,3,4,5,6',product.shipping_methods) ) THEN 1 ELSE 0 END) as available"),
        ]);
        if($this->to_country_id == 1){
            $shoppingCarts = $shoppingCarts->addSelect([
                DB::raw("(CASE WHEN '$this->to_country_id' = 1 and '$this->shipping_method_id' = 1 THEN product.airplane_days WHEN '$this->to_country_id' = 1 and '$this->shipping_method_id' != 1 THEN product.hotel_days ELSE null END) as stockDays"),
            ]);
        }
        $shoppingCarts = $shoppingCarts->orderBy('shopping_cart.create_time','asc')->get();
        if(count($shoppingCarts) > 0){
            foreach($shoppingCarts as $shoppingCart){
                $shoppingCart->digiwin_payment_id = $collectData['digiwin_payment_id'];
                if(!empty($shoppingCart->vendor_service_fee_percent)){
                    $serviceFees = $this->serviceFee($shoppingCart->vendor_service_fee_percent);
                    foreach($serviceFees as $serviceFee){
                        $serviceFee->name == 'iCarry' ? $shoppingCart->vendor_service_fee_percent = $serviceFee->percent : '';
                    }
                }else{
                    $shoppingCart->vendor_service_fee_percent = 0;
                }
                //採購價
                if(empty($shoppingCart->purchase_price) || $shoppingCart->purchase_price <= 0 ){
                    $shoppingCart->vendor_service_fee_percent <= 0 ? $shoppingCart->purchase_price = $shoppingCart->product_price : $shoppingCart->purchase_price = $shoppingCart->product_price - $shoppingCart->product_price * ( $shoppingCart->vendor_service_fee_percent / 100 );
                }
                if ($shoppingCart->available == 1) {
                    if ($this->to_country_id == 1) {
                        $shoppingCart->take_tiem = $this->take_time;
                        $shoppingCart->payTime = date('Ymd');
                        $shoppingCart->min_pay_time = date('Y-m-d');
                        $shoppingCart->min_pay_time = date('Y-m-d');
                        $this->shipping_method_id == 1 ? $shoppingCart->max_days = $shoppingCart->airplane_days : $shoppingCart->max_days = $shoppingCart->hotel_days;
                        $this->shipping_method_id == 1 ? $shoppingCart->max_receiver_key_time = $this->take_time : $shoppingCart->max_receiver_key_time = null; //機場提貨時間
                        $atLeastDay = $this->getProductAvailableDate($shoppingCart);
                        unset($shoppingCart->take_time);
                        unset($shoppingCart->payTime);
                        unset($shoppingCart->min_pay_time);
                        unset($shoppingCart->min_pay_time);
                        unset($shoppingCart->max_days);
                        unset($shoppingCart->max_receiver_key_time);
                        if (strtotime($atLeastDay) < strtotime($this->take_time)) {
                            $data['available'][] = $shoppingCart;
                        }
                    } else {
                        $data['available'][] = $shoppingCart;
                    }
                }
            }
        }
        return $data['available'];
    }
    //計算可結帳購物車
    private function calculation($availableProduct = [])
    {
        $data['totalAmount'] = $data['amount'] = $data['parcel_tax'] = $data['shipping_fee'] = $data['discount'] = $data['spend_point'] = 0;
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
        if(empty($shippingFee)){
            return 'shippingFeeFail';
        }
        if(count($availableProduct) > 0){
            $totalGrossWeight = 0;
            //計算商品總金額
            foreach ($availableProduct as $cart){
                $totalGrossWeight += $cart->gross_weight * $cart->quantity;
                $data['amount'] += $cart->price * $cart->quantity;
            }
            //若使用購物金則重算總金額 (購物金只能抵扣商品總金額)
            if(!empty($this->points) && $this->points == 1){
                $user = UserDB::find($this->userId)->points;
                if(!empty($user)){
                    $data['spend_point'] = $user->points;
                    if($data['spend_point'] > 0){
                        $amount = $data['amount'] - $data['spend_point'];
                        if($amount <= 0){
                            $data['spend_point'] = $data['amount'];
                            $data['amount'] = 0;
                        }else{
                            $data['amount'] = $amount;
                        }
                    }
                }
            }
            //折扣
            !empty($this->promotion_code) ? $data['discount'] = round($this->discount($data['amount'])) : '';
            //計算運費
            if($shippingFee->shipping_type == 'base'){
                $data['amount'] >= $shippingFee->free_shipping ? $data['shipping_fee'] = 0 : $data['shipping_fee'] = $shippingFee->shipping_base_price;
            }else{
                $data['shipping_fee'] = round(($totalGrossWeight / 1000) * $shippingFee->shipping_kg_price);
                $data['shipping_fee'] < $shippingFee->shipping_kg_price ? $data['shipping_fee'] = $shippingFee->shipping_kg_price : '';
            }
            //行郵稅
            $this->to_country_id != 1 ? $data['parcel_tax'] = round($data['amount'] * ($shippingFee->tax_rate / 100)) : $data['parcel_tax'] = 0;
        }
        $data['totalAmount'] = $data['amount'] + $data['shipping_fee'] + $data['parcel_tax'] - $data['discount'];
        return $data;
    }
    //折扣
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
    //資料收集
    private function collectData()
    {
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->storeRules))){
                $data[$key] = $value;
            }
        }
        //判斷來源
        if(!empty($data['domain'])){
            if(strpos($data['domain'],'icarry') !==false){
                $data['source'] = 'iCarry';
            }
        }
        //訂單編號規則 12碼+2
        $data['order_number']=date("ymdHis").rand(10,99);
        $data['status'] = 0;
        $data['user_id'] = $data['create_id'] = $this->userId;
        //置換
        $data['from'] = $data['from_country_id'];
        $data['origin_country'] = CountryDB::find($data['from_country_id'])->name;
        $data['to'] = $data['to_country_id'];
        $data['ship_to'] = CountryDB::find($data['to_country_id'])->name;

        //檢查使用者資料表中email欄位, 若無則更新
        if(!empty($data['buyer_email'])){
            $user = UserDB::find($this->userId);
            if(empty($user->email) || $user->email = '' || $user->email = null || $user->email = "new user's email"){
                $user->update(['email' => $data['buyer_email']]);
            }
        }
        if($data['create_type'] == 'web'){
            if($data['pay_method'] == '購物金'){
                $data['digiwin_payment_id'] = '999';
                $data['create_type'] = '其他';
            }else{
                $digiwinPayment = DigiwinPaymentDB::where('customer_name',$data['pay_method'])->first();
                if(!empty($digiwinPayment)){
                    $data['digiwin_payment_id'] = $digiwinPayment->customer_no;
                }else{
                    return 'payMethodFail';
                }
            }
        }else{
            $digiwinPayment = DigiwinPaymentDB::where('create_type',$data['create_type'])->first();
            if(!empty($digiwinPayment)){
                $data['digiwin_payment_id'] = $digiwinPayment->customer_no;
            }else{
                return 'createTypeFail';
            }
        }
        //shipping_method_id 切換
        if($this->from_country_id == $this->to_country_id){
            $this->shipping_method_id = 6;
        }elseif($this->from_country_id != 1 && $this->to_country_id == 1){
            $this->shipping_method_id = 5;
        }
        $data['shipping_method'] = $this->shipping_method_id;
        $this->shipping_method_id == 1 || $this->shipping_method_id == 2 ? $data['book_shipping_date'] = $this->bookShippingDate($this->take_time,$this->shipping_method_id) : $data['receiver_key_time'] = null;
        //收件人資料
        !empty($data['take_time']) ? $data['take_time'] = date('Y-m-d', strtotime($data['take_time'])) : $data['take_time'] = null;
        //國際碼處理
        if(!empty($data['receiver_nation_number'])){
            $data['receiver_nation_number'] != 'o' ? $data['receiver_nation_number'] = '+'.str_replace('+','',$data['receiver_nation_number']) : $data['receiver_nation_number'] = str_replace('+','',$data['receiver_nation_number']);
            if(!empty($data['receiver_phone_number'])){
                $data['receiver_nation_number'] == 'o' ? $data['receiver_phone_number'] = '+'.str_replace('+','',$data['receiver_phone_number']) : '';
            }
        }
        //其他聯絡方式
        if(!empty($data['receiver_other_contact']) && !empty($data['receiver_other_contact_value'])){
            if (!empty($data['user_memo'])) {
                $data['user_memo'] = $data['user_memo'].'。收件人其他聯絡方式'.$data['receiver_other_contact'].':'.$data['receiver_other_contact_value'];
            }else{
                $data['user_memo'] = '收件人其他聯絡方式'.$data['receiver_other_contact'].':'.$data['receiver_other_contact_value'];
            }
        }
        if(!empty($this->shipping_method_id == 1)){
            if ($this->to_country_id == 1) {
                $data['receiver_keyword'] = $data['airport_flight_number'];
                $data['receiver_address'] = $data['airport_pickup_location'];
                $data['receiver_tel'] = $data['receiver_nation_number'].$data['receiver_phone_number'];
            }
        }elseif(!empty($this->shipping_method_id == 2)){
            if ($this->to_country_id == 1) {
                $data['receiver_keyword'] = 'Hotel:'.$data['hotel_name'].(!empty($data['hotel_room_number']) ? '   Room:'.$data['hotel_room_number'] : '').(!empty($data['hotel_checkout_date']) ? '   Checkout: '.date('Y-m-d', strtotime($data['hotel_checkout_date'])) : '');
                $data['receiver_address'] = $data['hotel_address'];
                $data['receiver_tel'] = $data['receiver_nation_number'].$data['receiver_phone_number'];
            }
        }elseif($this->shipping_method_id >=  4){
            $userAddress = UserAddressDB::where([['user_id',$this->userId],['country',$data['ship_to']]])
            ->select([
                '*',
                DB::raw("IF(phone IS NULL,'',AES_DECRYPT(phone,'$this->aesKey')) as phone"),
            ])->find($this->user_address_id);
            if(!empty($userAddress)){
                $data['receiver_name'] = $userAddress->name;
                $data['receiver_id_card'] = $userAddress->id_card;
                $data['receiver_nation_number'] = $userAddress->nation;
                $data['receiver_phone_number'] = $userAddress->phone;
                $data['receiver_tel'] = $userAddress->nation.$userAddress->phone;
                $data['receiver_email'] = $userAddress->email;
                $data['receiver_province'] = $userAddress->province;
                $data['receiver_city'] = $userAddress->city;
                $data['receiver_area'] = $userAddress->area;
                $data['receiver_address'] = $userAddress->address;
                $data['receiver_zip_code'] = $userAddress->zip_code;
                $data['china_id_img1'] = $userAddress->china_id_img1;
                $data['china_id_img2'] = $userAddress->china_id_img2;
            }else{
                return 'userAddressFail';
            }
        }
        //發票資料處理
        $data['love_code'] = $data['invoice_address'] = null;
        $data['print_flag'] = 'N';
        if($data['invoice_sub_type'] == 1){
            $data['love_code'] = 86888;
            $data['invoce_type'] = 0;
            $data['carrier_num'] = $data['carrier_type'] = $data['invoice_title'] = $data['invoice_number'] = null;
        }elseif($data['invoice_sub_type'] == 2){
            $data['invoce_type'] = 2;
            $data['invoice_title'] = $data['invoice_number'] = null;
            !empty($data['carrier_num']) ? $data['carrier_num'] = strtoupper($data['carrier_num']) : '';
        }elseif($data['invoice_sub_type'] == 3){
            $data['print_flag'] = 'Y';
            $data['invoce_type'] = 3;
            $data['carrier_num'] = $data['carrier_type'] = null;
        }
        //其它資料
        empty($data['exchange_rate']) ? $data['exchange_rate'] = SystemSettingDB::first()->exchange_rate_RMB : '';
        //清除無用變數
        unset($data['from_country_id'],$data['to_country_id'],$data['take_time'],$data['shipping_method_id'],$data['points'],$data['agree'],$data['user_address_id'],$data['airport_pickup_location'],$data['airport_flight_number'],$data['hotel_checkout_date'],$data['hotel_name'],$data['hotel_address'],$data['receiver_other_contact'],$data['receiver_other_contact_value'],$data['receiver_other_contact_value']);
        return $data;
    }
    //移動可結帳購物車
    private function moveShoppingCartToOrderItem($available = [])
    {
        // 訂單商品處理
        if(count($available) > 0){
            foreach($available as $item){
                $shoppingCartId = $item->id;
                unset($item->id);
                unset($item->product_id);
                $item->order_id = $this->orderId;
                $orderItem = OrderItemDB::create($item->toArray());
                $orderItem->sku = $item->sku;
                //組合品建立
                if(strstr($item->sku,'BOM')){
                    $packageData = json_decode($item->package_data);
                    foreach($packageData as $package){
                        if(isset($package->is_del)){
                            $this->createPackageData($package,$orderItem);
                        }else{
                            $this->createPackageData($package,$orderItem);
                        }
                    }
                }
                if (env('APP_ENV') == 'production') { //測試機不移除
                    $shoppingCart = ShoppingCartDB::find($shoppingCartId)->delete();
                }
            }
        }
    }
    //亞洲萬里通
    private function asiamiles($totalAmount = 0)
    {
        if($totalAmount >= 3000){
            $user = UserDB::find($this->userId);
            if(!empty($user->asiamiles_account)){
                OrderAsiamilesDB::create([
                    'order_id' => $this->orderId,
                    'asiamiles_account' => $user->asiamiles_account,
                    'asiamiles_name' => $user->asiamiles_name,
                    'asiamiles_last_name' => $user->asiamiles_last_name,
                ]);
            }
        }
        return null;
    }
    //購物金處理
    private function points($order,$pointType, $return = false)
    {
        if($order->spend_point > 0){
            $user = UserDB::find($this->userId);
            $user->update(['points' => $user->points + ($return == true ? $order->spend_point : -$order->spend_point)]);
            UserPointDB::create([
                'user_id' => $this->userId,
                'point_type' => $pointType,
                'points' => $return == true ? $order->spend_point : -$order->spend_point,
                'balance' => $user->points + ($return == true ? $order->spend_point : -$order->spend_point),
            ]);
        }
    }
    //庫存處理
    private function stocks($available, $return = false)
    {
        if(count($available) > 0){
            foreach($available as $item){
                $product = ProductModelDB::find($item->product_model_id);
                $return ? $balance = $product->quantity + $item->quantity : $balance = $product->quantity - $item->quantity;
                $product->update(['quantity' => $balance]);
            }
        }
    }
    //金流處理
    private function pay($order = [])
    {
        $provider = $this->payProvider[$this->pay_method];
        //ACpay 金流
        if($provider == 'acpay'){
            $result['type'] = 'url';
            $result['provider'] = 'acpay';
            $result['payUrl'] = $this->ACPay($this->orderNumber,$this->totalAmount,$this->buyerEmail,$this->returnURL);
        }

        //智付通付款金流
        if($provider == 'spgateways'){
            $result['type'] = 'form';
            $result['data'] = $this->newebPay($this->pay_method,$this->orderNumber,$this->totalAmount,$this->buyerEmail);
        }
        //玉山銀行付款金流
        if($provider == 'esun'){
            if($this->pay_method == '玉山支付寶'){
                $result['type'] = 'url';
            }else{
                $result['type'] = 'form';
            }
            $result['data'] = $this->esunPay($this->pay_method,$this->orderNumber,$this->totalAmount);
        }
        //台新銀行付款金流
        if($provider == 'taishin'){
            $result = $this->taishinPay($this->pay_method,$this->orderNumber,$this->totalAmount,$this->buyerEmail);
        }
        return $result;
    }

        /*
        整理Servce_fee資料
        1. 檢驗是否存在
        2. 檢驗是否為陣列
        3. 轉換percent空值為0
    */
    private function serviceFee($input = ''){
        if($input == ''){
            $serviceFees = json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]');
        }elseif(is_array($input)){
            for($i=0;$i<count($input['name']);$i++){
                $serviceFees[$i]['name'] = $input['name'][$i];
                $serviceFees[$i]['percent'] = $input['percent'][$i];
            }
            $serviceFees = json_encode($serviceFees);
        }else{
            $serviceFees = json_decode(str_replace('"percent":}','"percent":0}',$input));
        }
        return $serviceFees;
    }

    private function createPackageData($package,$orderItem){
        if($package->bom == $orderItem->sku){
            foreach($package->lists as $list){
                $useQty = $list->quantity;
                $pm = ProductModelDB::join($this->productTable,$this->productTable.'.id',$this->productModelTable.'.product_id')
                ->join($this->vendorTable,$this->vendorTable.'.id',$this->productTable.'.vendor_id')
                ->where('sku',$list->sku)
                ->select([
                    $this->productModelTable.'.id as product_model_id',
                    $this->productModelTable.'.sku',
                    $this->productModelTable.'.digiwin_no',
                    $this->productTable.'.id as product_id',
                    DB::raw("CONCAT($this->vendorTable.name,' ',$this->productTable.name,'-',$this->productModelTable.name) as product_name"),
                    $this->productTable.'.unit_name',
                    $this->productTable.'.price',
                    $this->productTable.'.gross_weight',
                    $this->productTable.'.net_weight',
                    $this->productTable.'.direct_shipment',
                    $this->productTable.'.is_tax_free',
                    $this->productTable.'.vendor_price',
                    $this->productTable.'.service_fee_percent as product_service_fee_percent',
                    $this->productTable.'.package_data',
                    $this->vendorTable.'.id as vendor_id',
                    $this->vendorTable.'.name as vendor_name',
                    $this->vendorTable.'.service_fee as vendor_service_fee',
                    $this->vendorTable.'.shipping_verdor_percent',
                ])->first();
                if(!empty($pm)){
                    OrderItemPackageDB::create([
                        'order_id' => $orderItem->order_id,
                        'order_item_id' => $orderItem->id,
                        'product_model_id' => $pm->product_model_id,
                        'sku' => $pm->sku,
                        'digiwin_no' => $pm->digiwin_no,
                        'digiwin_payment_id' => $orderItem->digiwin_payment_id,
                        'gross_weight' => $this->grossWeightRate * $pm->gross_weight,
                        'net_weight' => $pm->net_weight,
                        'quantity' => $useQty * $orderItem->quantity,
                        'is_del' => 0,
                        'product_name' => $pm->product_name,
                        'purchase_price' => 0,
                        'direct_shipment' => $pm->direct_shipment,
                    ]);
                }
            }
        }
    }
}
