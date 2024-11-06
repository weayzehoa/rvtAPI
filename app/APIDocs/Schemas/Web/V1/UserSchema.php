<?php
/**
 * @OA\Schema(
 *     schema="userSuccess",
 *     type="object",
 *     title="前台-使用者資料欄位說明"
 * )
 */
class userSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="更新成功。")
     */
    public $message;
    /**
     * 資料內容
     * @var User[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="前台-使用者資料欄位說明"
 * )
 */
class User
{
    /**
     * The id of the users table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $id;
    /**
     * 使用者名稱
     * @var string
     * @OA\Property(format="string", example="Roger Wu")
     */
    public $name;
    /**
     * 國際碼
     * @var string
     * @OA\Property(format="string", example="+886")
     */
    public $nation;
    /**
     * 手機號碼
     * @var string
     * @OA\Property(format="int64", example="987654321")
     */
    public $mobile;
    /**
     * 電子郵件 (type = profile 顯示)
     * @var string
     * @OA\Property(format="string", example="icarry@icarry.me")
     */
    public $email;
    /**
     * 地址 (type = profile 顯示)
     * @var string
     * @OA\Property(format="string", example="台灣台北市南京東路三段103號11樓之1")
     */
    public $address;
    /**
     * 推薦者 (type = profile 顯示)
     * @var string
     * @OA\Property(format="string", example="4588")
     */
    public $refer_id;
    /**
     * 亞洲萬里通帳號 (type = profile 顯示)
     * @var integer
     * @OA\Property(format="int16", example="1669943016")
     */
    public $asiamiles_account;
    /**
     * 目前購物金 (type = points 顯示)
     * @var integer
     * @OA\Property(format="int16", example="200")
     */
    public $points;
    /**
     * 近期到期購物金 (type = points 顯示)
     * @var integer
     * @OA\Property(format="int16", example="100")
     */
    public $expiring_points;
    /**
     * 近期購物金到期時間 (type = points 顯示)
     * @var string
     * @OA\Property(format="date", example="2021-12-15 15:00:50")
     */
    public $points_dead_time;
    /**
     * 購物金歷史 (type = points 顯示)
     * @var userPoints[]
     * @OA\Property()
     */
    public $points_history;
    /**
     * 最愛產品列表 (type = favorites 顯示)
     * @var userFavoriteProducts[]
     * @OA\Property()
     */
    public $favorite_products;
    /**
     * 常用收件人列表 (type = address 顯示)
     * @var userAddress[]
     * @OA\Property()
     */
    public $user_address;
    /**
     * 使用者訂單列表 (限100筆) (type = orders 顯示)
     * @var userOrders[]
     * @OA\Property()
     */
    public $user_orders;
}

/**
 * @OA\Schema(
 *     schema="userFavoriteProducts",
 *     type="object",
 *     title="前台-使用者最愛產品列表欄位說明"
 * )
 */
class userFavoriteProducts
{
    /**
     * The id of the users table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $user_id;
    /**
     * The id of the products table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $product_id;
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $vendor_id;
    /**
     * 產品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="大甲師 - 芋頭流芯酥(6入)")
     */
    public $name;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $vendor_name;
    /**
     * 商品照片
     * @var integer
     * @OA\Property(format="integer", example="ttps://dev-cdn.icarry.me/upload/product/photo1_2638_1588751006.png")
     */
    public $image;
}

/**
 * @OA\Schema(
 *     schema="userPoints",
 *     type="object",
 *     title="前台-使用者購物金列表欄位說明"
 * )
 */
class userPoints
{
    /**
     * The id of the users table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $user_id;
    /**
     * 購物金
     * @var integer
     * @OA\Property(format="int64", example="200")
     */
    public $points;
    /**
     * 購物金說明
     * @var string
     * @OA\Property(format="string", example="推薦 67797 成功，贈送 100 點")
     */
    public $point_type;
    /**
     * 使用/獲得日期
     * @var string
     * @OA\Property(format="date", example="2021-12-15")
     */
    public $create_time;
}

/**
 * @OA\Schema(
 *     schema="userOrders",
 *     type="object",
 *     title="前台-使用者訂單列表資料欄位說明"
 * )
 */
class userOrders
{
    /**
     * The id of the orders table
     * @var integer
     * @OA\Property(format="int64", example="14588")
     */
    public $id;
    /**
     * The id of the users table
     * @var integer
     * @OA\Property(format="int64", example="4588")
     */
    public $user_id;
    /**
     * 訂單號碼
     * @var integer
     * @OA\Property(format="int64", example="21032515123190")
     */
    public $order_number;
    /**
     * 訂單金額
     * @var integer
     * @OA\Property(format="int64", example="500")
     */
    public $price;
    /**
     * 訂單建立日期
     * @var string
     * @OA\Property(format="date", example="2021-03-25")
     */
    public $create_date;
    /**
     * 付款方式
     * @var string
     * @OA\Property(format="date", example="信用卡")
     */
    public $pay_method;
    /**
     * 訂單狀態 (文字)
     * @var string
     * @OA\Property(format="date", example="集貨中")
     */
    public $order_status;
    /**
     * 訂單狀態 (數字) (-1:已取消 0:尚未付款 1:待出貨 2:集貨中 3:已出貨 4:已完成)
     * @var string
     * @OA\Property(format="date", example="2")
     */
    public $status;
    /**
     * 寄送國家id
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $to_country_id;
    /**
     * 寄送國家 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="date", example="台灣")
     */
    public $ship_to;
    /**
     * 收件者名字
     * @var string
     * @OA\Property(format="string", example="iCarry")
     */
    public $receiver_name;
    /**
     * 收件人地址
     * @var string
     * @OA\Property(format="date", example="台灣 新北市 新莊區  新泰路xxx巷xx號x樓, 242")
     */
    public $receiver_address;
    /**
     * 商品總數量
     * @var integer
     * @OA\Property(format="int64", example="10")
     */
    public $totalItems;
    /**
     * 商品照片
     * @var itemsImage[]
     * @OA\Property()
     */
    public $items_image;
}
/**
 * @OA\Schema(
 *     schema="itemsImage",
 *     type="object",
 *     title="前台-使用者訂單列表-商品照片資料欄位說明"
 * )
 */
class itemsImage
{
    /**
     * 訂單id
     * @var integer
     * @OA\Property(format="int64", example="21032")
     */
    public $order_id;
    /**
     * 商品照片
     * @var integer
     * @OA\Property(format="integer", example="ttps://dev-cdn.icarry.me/upload/product/photo1_2638_1588751006.png")
     */
    public $image;
}
