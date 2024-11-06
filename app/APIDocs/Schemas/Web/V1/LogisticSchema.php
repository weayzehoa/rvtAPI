<?php
/**
 * @OA\Schema(
 *     schema="LogisticSuccess",
 *     type="object",
 *     title="通用-物流資料列表欄位說明"
 * )
 */
class LogisticSuccess
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
     * @var LogisticList[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="LogisticList",
 *     type="object",
 *     title="通用-物流資料欄位說明"
 * )
 */
class LogisticList
{
    /**
     * 發(寄)出國家
     * @var string
     * @OA\Property(format="int64", example="台灣")
     */
    public $ship_from;
    /**
     * 寄送列表
     * @var ShipToList[]
     * @OA\Property()
     */
    public $ship_to_list;
}

/**
 * @OA\Schema(
 *     schema="ShipToList",
 *     type="object",
 *     title="通用-物流資料-寄送列表欄位說明"
 * )
 */
class ShipToList
{
    /**
     * 目的地國家id
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $to_country_id;
    /**
     * 物流類型 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="指定地址")
     */
    public $logistic_type;
    /**
     * 目的地國家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="台灣")
     */
    public $ship_to;
    /**
     * 描述
     * @var string
     * @OA\Property(format="string", example="訂單成立後（即付款完成後）{0} 個工作日內會完成出貨。配送天數約 5-9 工作天。")
     */
    public $description;
    /**
     * 機場提貨地址 (logistic_type = 當地機場 出現)
     * @var AirportPickupLocation[]
     * @OA\Property()
     */
    public $airport_pickup_location;
}

/**
 * @OA\Schema(
 *     schema="AirportPickupLocation",
 *     type="object",
 *     title="通用-物流資料-寄送列表-機場提貨地址欄位說明"
 * )
 */
class AirportPickupLocation
{
    /**
     * The id of Airport Address Table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 機場名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="桃園機場/第一航廈出境大廳門口")
     */
    public $name;
    /**
     * 機場提貨開始時間
     * @var string
     * @OA\Property(format="string", example="06:00")
     */
    public $pickup_time_start;
    /**
     * 機場提貨結束時間
     * @var string
     * @OA\Property(format="string", example="23:59")
     */
    public $pickup_time_end;
}
