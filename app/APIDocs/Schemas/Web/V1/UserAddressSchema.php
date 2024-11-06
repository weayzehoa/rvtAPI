<?php
/**
 * @OA\Schema(
 *     schema="userAddressList",
 *     type="object",
 *     title="前台-使用者常用地址列表欄位說明"
 * )
 */
class userAddressList
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
     * @var userAddress[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="userAddressShow",
 *     type="object",
 *     title="前台-使用者常用地址資料欄位說明"
 * )
 */
class userAddressShow
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * 主要資料表
     * @var string
     * @OA\Property(format="string", example="user_address")
     */
    public $model;
    /**
     * 資料內容
     * @var userAddress[]
     * @OA\Property()
     */
    public $data;
}


/**
 * @OA\Schema(
 *     schema="userAddress",
 *     type="object",
 *     title="前台-使用者常用地址資料欄位說明"
 * )
 */
class userAddress
{
    /**
     * The id of the user_addresss table
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
     * 收件人名字
     * @var string
     * @OA\Property(format="string", example="直流電通")
     */
    public $name;
    /**
     * 國際碼
     * @var string
     * @OA\Property(format="string", example="+886")
     */
    public $nation;
    /**
     * 電話
     * @var string
     * @OA\Property(format="string", example="222110002")
     */
    public $phone;
    /**
     * 國家
     * @var string
     * @OA\Property(format="string", example="中國")
     */
    public $country;
    /**
     * 城市
     * @var string
     * @OA\Property(format="string", example="黑龙江省")
     */
    public $city;
    /**
     * 區域
     * @var string
     * @OA\Property(format="string", example="哈尔滨市")
     */
    public $area;
    /**
     * 區
     * @var string
     * @OA\Property(format="string", example="南岗区")
     */
    public $s_area;
    /**
     * 地址
     * @var string
     * @OA\Property(format="string", example="南京東路三段103號11樓之1")
     */
    public $address;
    /**
     * 大陸身分證圖片正面
     * @var string
     * @OA\Property(format="string", example="https://china-customs-id.s3.ap-northeast-1.amazonaws.com/20210608213554.jpg")
     */
    public $china_id_img1;
    /**
     * 大陸身分證圖片反面
     * @var string
     * @OA\Property(format="string", example="https://china-customs-id.s3.ap-northeast-1.amazonaws.com/20210608213556.jpg")
     */
    public $china_id_img2;
    /**
     * 預設常用
     * @var integer
     * @OA\Property(format="int64", example="1")
     */
    public $is_default;
}
