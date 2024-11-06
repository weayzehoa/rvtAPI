<?php
/**
 * @OA\Schema(
 *     schema="ReceiverBaseSuccess",
 *     type="object",
 *     title="前台-提貨日資料列表欄位說明"
 * )
 */
class ReceiverBaseSuccess
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
     * @var ReceiverBase[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="ReceiverBase",
 *     type="object",
 *     title="前台-提貨日資料欄位說明"
 * )
 */
class ReceiverBase
{
    /**
     * 日期
     * @var string
     * @OA\Property(format="string", example="2021-01-01")
     */
    public $date;
    /**
     * 星期 (0:星期天)
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $week;
    /**
     * 可否提貨 (0:不可, 1:可)
     * @var string
     * @OA\Property(format="int64", example="1")
     */
    public $pickup;
    /**
     * 無法提貨提示
     * @var string
     * @OA\Property(format="string", example="假日物流不派送")
     */
    public $pickup_memo;
}
