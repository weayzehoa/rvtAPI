<?php
/**
 * @OA\Schema(
 *     schema="PromoBoxSuccess",
 *     type="object",
 *     title="前台-優惠活動資料列表欄位說明"
 * )
 */
class CategorySuccess
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
     * @var PromoBox[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="PromoBox",
 *     type="object",
 *     title="前台-優惠活動資料欄位說明"
 * )
 */
class PromoBox
{
    /**
     * The id of the promo_boxes table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 活動標題
     * @var string
     * @OA\Property(format="string", example="台灣Pay，送禮 roger U")
     */
    public $title;
    /**
     * 介紹文(前)
     * @var string
     * @OA\Property(format="string", example="2021/06/01 至 2021/10/31 輸入促銷代碼「TWPay」並使用「台灣Pay」(限金融卡/帳戶)掃碼支付成功，可享商品金額15%現折")
     */
    public $teaser;
    /**
     * 詳細內文(後) 請將此欄位與teaser欄位組合成完整資料
     * @var string
     * @OA\Property(format="string", example="（僅以最終商品金額計算，運費、關稅、其他稅務恕不折抵），每筆交易最高現折新臺幣300元。")
     */
    public $content;
    /**
     * 圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/upload/category/cover_1_1490589933.jpg")
     */
    public $image;
}
