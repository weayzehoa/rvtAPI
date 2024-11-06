<?php
/**
 * @OA\Schema(
 *     schema="CountrySuccess",
 *     type="object",
 *     title="通用-國家資料列表欄位說明"
 * )
 */
class CountrySuccess
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
     * @var Country[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="Country",
 *     type="object",
 *     title="通用-國家資料欄位說明"
 * )
 */
class Country
{
    /**
     * The id of the countries table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 國家中文名稱
     * @var string
     * @OA\Property(format="string", example="台灣")
     */
    public $name;
    /**
     * 語言代碼
     * @var string
     * @OA\Property(format="string", example="tw")
     */
    public $lang;
    /**
     * 國際碼
     * @var string
     * @OA\Property(format="string", example="86")
     */
    public $code;
    /**
     * 排序
     * @var integer
     * @OA\Property(format="float", example="1")
     */
    public $sort;
}
