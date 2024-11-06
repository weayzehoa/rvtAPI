<?php
/**
 * @OA\Schema(
 *     schema="shippingMethodSuccess",
 *     type="object",
 *     title="通用-寄送方式資料列表欄位說明"
 * )
 */
class shippingMethodSuccess
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
     * @var shippingMethod[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="shippingMethod",
 *     type="object",
 *     title="通用-寄送方式資料欄位說明"
 * )
 */
class shippingMethod
{
    /**
     * The id of the shipping_methods table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 分類名稱
     * @var string
     * @OA\Property(format="string", example="機場提貨")
     */
    public $name;
}
