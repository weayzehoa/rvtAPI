<?php
/**
 * @OA\Schema(
 *     schema="OrderListSuccess",
 *     type="object",
 *     title="前台-使用者訂單資料列表欄位說明"
 * )
 */
class OrderListSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="null")
     */
    public $message;
    /**
     * 資料筆數總計
     * @var integer
     * @OA\Property(format="integer", example="10")
     */
    public $total;
    /**
     * 資料內容
     * @var OrderListData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="OrderListData",
 *     type="object",
 *     title="前台-使用者訂單列表資料欄位說明"
 * )
 */

 class OrderListData
{
    /**
     * 訂單id
     * @var integer
     * @OA\Property(format="integer", example="119524")
     */
    public $id;
    /**
     * 使用者id
     * @var integer
     * @OA\Property(format="integer", example="84533")
     */
    public $user_id;
    /**
     * 訂單號碼
     * @var integer
     * @OA\Property(format="string", example="1234567890123456")
     */
    public $order_number;
    /**
     * 建立日期
     * @var string
     * @OA\Property(format="date", example="2021-06-01")
     */
    public $create_date;
    /**
     * 訂單金額
     * @var integer
     * @OA\Property(format="integer", example="4321")
     */
    public $price;
    /**
     * 訂單狀態 (語言自動變更)
     * @var string
     * @OA\Property(format="string", example="尚未付款")
     */
    public $order_status;
    /**
     * 訂單狀態
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $status;
    /**
     * 寄送國家id
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $to_country_id;
    /**
     * 寄送國家名稱 (語言自動變更)
     * @var string
     * @OA\Property(format="string", example="台灣")
     */
    public $ship_to;
    /**
     * 收件者名字
     * @var string
     * @OA\Property(format="string", example="roger")
     */
    public $receiver_name;
    /**
     * 收件者地址
     * @var string
     * @OA\Property(format="string", example="台灣 台北市 南京東路三段113號11樓之1")
     */
    public $receiver_address;
    /**
     * 商品數量
     * @var integer
     * @OA\Property(format="integer", example="2")
     */
    public $totalItems;
}

/**
 * @OA\Schema(
 *     schema="OrderShowSuccess",
 *     type="object",
 *     title="前台-使用者訂單顯示資料欄位說明"
 * )
 */

 class OrderShowSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * 資料表名稱
     * @var string
     * @OA\Property(format="string", example="orders")
     */
    public $model;
    /**
     * 訂單id
     * @var integer
     * @OA\Property(format="integer", example="119522")
     */
    public $id;
    /**
     * 資料內容
     * @var OrderData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="OrderData",
 *     type="object",
 *     title="前台-使用者訂單資料欄位說明"
 * )
 */
 class OrderData
{
    /**
     * 訂單id
     * @var integer
     * @OA\Property(format="integer", example="119524")
     */
    public $id;
    /**
     * 使用者id
     * @var integer
     * @OA\Property(format="integer", example="84533")
     */
    public $user_id;
    /**
     * 訂單號碼
     * @var integer
     * @OA\Property(format="string", example="1234567890123456")
     */
    public $order_number;
    /**
     * 訂單狀態 (語言自動變更)
     * @var string
     * @OA\Property(format="string", example="尚未付款")
     */
    public $order_status;
    /**
     * 訂單狀態
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $status;
    /**
     * 寄送國家id
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $from;
    /**
     * 目的地國家id
     * @var integer
     * @OA\Property(format="integer", example="2")
     */
    public $to;
    /**
     * 預估寄達天數
     * @var string
     * @OA\Property(format="string", example="2-7")
     */
    public $estimate_ship_dates;
    /**
     * 寄送方式文字 (語言自動變更)
     * @var integer
     * @OA\Property(format="integer", example="寄送海外")
     */
    public $shipping_method_text;
    /**
     * 寄送方式id
     * @var integer
     * @OA\Property(format="integer", example="4")
     */
    public $shipping_method;
    /**
     * 預計寄送日期 (語言自動變更) (目的地為台灣地區、機場提貨或旅店提貨顯示)
     * @var string
     * @OA\Property(format="date", example="2021-06-23")
     */
    public $book_shipping_date;
    /**
     * 航班/旅店資訊 (目的地為台灣地區、機場提貨或旅店提貨顯示)
     * @var string
     * @OA\Property(format="string", example="BR-123")
     */
    public $receiver_keyword;
    /**
     * 航班/旅店日期資訊 (目的地為台灣地區、機場提貨或旅店提貨顯示)
     * @var string
     * @OA\Property(format="date", example="2018-09-04 14:00")
     */
    public $receiver_key_time;
    /**
     * 付款方式 (語言自動變換)
     * @var string
     * @OA\Property(format="string", example="BR-123")
     */
    public $pay_method;
    /**
     * 收件者名字
     * @var string
     * @OA\Property(format="string", example="roger")
     */
    public $receiver_name;
    /**
     * 收件者電話
     * @var string
     * @OA\Property(format="string", example="+886906486688")
     */
    public $receiver_phone;
    /**
     * 收件者電子郵件
     * @var string
     * @OA\Property(format="string", example="roger@rvt.idv.tw")
     */
    public $receiver_email;
    /**
     * 收件者地址
     * @var string
     * @OA\Property(format="string", example="台灣台北市中山區南京東路三段103號11樓之1")
     */
    public $receiver_address;
    /**
     * 使用者備註
     * @var string
     * @OA\Property(format="string", example="越快越好")
     */
    public $user_memo;
    /**
     * 發票類別 (語言自動變換)
     * @var string
     * @OA\Property(format="string", example="二聯式")
     */
    public $invoice_type;
    /**
     * 發票資訊, 1捐贈、2個人、3公司
     * @var string
     * @OA\Property(format="string", example="1")
     */
    public $invoice_sub_type;
    /**
     * 載具類別, 0=手機條碼載具 1=自然人憑證條碼載具 2=智付寶載具
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $carrier_type;
    /**
     * 手機條碼/自然人憑證條碼
     * @var string
     * @OA\Property(format="string", example="/EEAW2F2")
     */
    public $carrier_num;
    /**
     * 此訂單開立發票號碼
     * @var string
     * @OA\Property(format="string", example="EE1234567")
     */
    public $is_invoice_no;
    /**
     * 發票抬頭
     * @var string
     * @OA\Property(format="string", example="公司名稱")
     */
    public $invoice_title;
    /**
     * 發票開立日期時間
     * @var string
     * @OA\Property(format="date", example="2018-10-22 18:05:48")
     */
    public $invoice_time;
    /**
     * 商品總金額
     * @var integer
     * @OA\Property(format="integer", example="8888")
     */
    public $amount;
    /**
     * 使用購物金
     * @var integer
     * @OA\Property(format="integer", example="100")
     */
    public $spend_point;
    /**
     * 運費
     * @var integer
     * @OA\Property(format="integer", example="100")
     */
    public $shipping_fee;
    /**
     * 跨境稅 (寄送海外時)
     * @var integer
     * @OA\Property(format="integer", example="100")
     */
    public $parcel_tax;
    /**
     * 折扣金額
     * @var integer
     * @OA\Property(format="integer", example="100")
     */
    public $discount;
    /**
     * 訂單總金額 (商品總金額+運費+跨境稅-折扣金額-使用購物金)
     * @var integer
     * @OA\Property(format="integer", example="8000")
     */
    public $price;
    /**
     * 訂單總毛重 (kg)
     * @var integer
     * @OA\Property(format="integer", example="0.35")
     */
    public $gross_weight;
    /**
     * 物流類別 (base = 固定運費, kg = 以公斤計算)
     * @var string
     * @OA\Property(format="string", example="base")
     */
    public $shippingType;
    /**
     * 免運門檻 (shippingType = base 時顯示)
     * @var integer
     * @OA\Property(format="integer", example="1800")
     */
    public $freeShipping;
    /**
     * 物流基本運費價格 (shippingType = kg 時，代表每公斤價格，超過一公斤以一公斤計算)
     * @var integer
     * @OA\Property(format="integer", example="200")
     */
    public $shippingPrice;
    /**
     * 跨境稅率% (非台灣地區顯示)
     * @var integer
     * @OA\Property(format="integer", example="200")
     */
    public $shippingTaxRate;
    /**
     * 物流資訊
     * @var string
     * @OA\Property(format="string", example="訂單成立後（即付款完成後）2-7 個工作日內會完成出貨。配送時間約為5～10工作天。")
     */
    public $shippingDescription;
    /**
     * 訂單建立日期
     * @var string
     * @OA\Property(format="date", example="2021-01-01")
     */
    public $create_date;
    /**
     * 預計出貨日
     * @var string
     * @OA\Property(format="date", example="2021-01-01")
     */
    public $expected_shipping_date;
    /**
     * 預計送達日 (只有台灣地區才顯示)
     * @var string
     * @OA\Property(format="date", example="2021-01-02")
     */
    public $expected_arrival_date;
    /**
     * 商品數量
     * @var integer
     * @OA\Property(format="integer", example="4")
     */
    public $total_items;
    /**
     * 物流資料內容
     * @var orderShippings[]
     * @OA\Property()
     */
    public $order_shippings;
    /**
     * 資料內容
     * @var OrderItems[]
     * @OA\Property()
     */
    public $order_items;
}

/**
 * @OA\Schema(
 *     schema="orderShippings",
 *     type="object",
 *     title="前台-使用者訂單寄送資料欄位說明"
 * )
 */
 class orderShippings
{
    /**
     * 寄送id (order_shippings table id)
     * @var integer
     * @OA\Property(format="integer", example="11952")
     */
    public $id;
    /**
     * 訂單id
     * @var integer
     * @OA\Property(format="integer", example="119522")
     */
    public $order_id;
    /**
     * 運送公司名稱
     * @var string
     * @OA\Property(format="integer", example="台灣宅配通")
     */
    public $express_way;
    /**
     * 運送單號
     * @var string
     * @OA\Property(format="integer", example="878070725560")
     */
    public $express_no;
    /**
     * 運送單號查詢網址
     * @var string
     * @OA\Property(format="integer", example="https://api.rvt.idv.tw/shipping_vendor_query.php?vendor=tpe&no=878070725560")
     */
    public $check_url;
}

/**
 * @OA\Schema(
 *     schema="OrderItems",
 *     type="object",
 *     title="前台-使用者訂單商品資料欄位說明"
 * )
 */
 class OrderItems
{
    /**
     * 商家名稱 (語言自動變換)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $vendor_name;
    /**
     * 商品名稱 (語言自動變換)
     * @var string
     * @OA\Property(format="string", example="原味鳳梨酥禮盒(6入)")
     */
    public $product_name;
    /**
     * 數量
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $quantity;
    /**
     * 建議售價 (0 則不顯示)
     * @var integer
     * @OA\Property(format="integer", example="182")
     */
    public $fake_price;
    /**
     * 單價
     * @var integer
     * @OA\Property(format="integer", example="250")
     */
    public $price;
    /**
     * 小計 (數量 * 單價)
     * @var integer
     * @OA\Property(format="integer", example="182")
     */
    public $amount_price;
    /**
     * 商品照片
     * @var integer
     * @OA\Property(format="integer", example="ttps://dev-cdn.rvt.idv.tw/upload/product/photo1_2638_1588751006.png")
     */
    public $image;
}
