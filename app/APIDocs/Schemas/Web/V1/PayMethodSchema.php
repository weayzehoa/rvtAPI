<?php
/**
 * @OA\Schema(
 *     schema="PayMethodListSuccess",
 *     type="object",
 *     title="前台-付款方式資料列表欄位說明"
 * )
 */
class PayMethodListSuccess
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
     * @var PayMethod[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="PayMethod",
 *     type="object",
 *     title="前台-付款方式資料欄位說明"
 * )
 */
class PayMethod
{
    /**
     * 顯示名稱
     * @var string
     * @OA\Property(format="string", example="信用卡")
     */
    public $name;
    /**
     * 類別
     * @var string
     * @OA\Property(format="string", example="信用卡")
     */
    public $type;
    /**
     * 值(value) (帶至後端用)
     * @var string
     * @OA\Property(format="string", example="智付通信用卡")
     */
    public $value;
    /**
     * 排序
     * @var string
     * @OA\Property(format="string", example="2")
     */
    public $sort;
    /**
     * 圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.icarry.me/upload/category/logo_1.jpg")
     */
    public $image;
}
