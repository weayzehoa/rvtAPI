<?php
/**
 * @OA\Schema(
 *     schema="AirportLocationSuccess",
 *     type="object",
 *     title="前台-機場地址資料列表欄位說明"
 * )
 */
class AirportLocationSuccess
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
     * @var AirportLocationData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="AirportLocationData",
 *     type="object",
 *     title="前台-機場地址資料欄位說明"
 * )
 */
class AirportLocationData
{
    /**
     * 名稱 (顯示)
     * @var string
     * @OA\Property(format="string", example="Taiwan Taoyuan Airport - Terminal 1 Departure Hall")
     */
    public $name;
    /**
     * 值 (value 帶入資料中)
     * @var string
     * @OA\Property(format="string", example="桃園機場/第一航廈出境大廳門口")
     */
    public $value;
}
