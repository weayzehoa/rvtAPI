<?php
/**
 * @OA\Schema(
 *     schema="CategorySuccess",
 *     type="object",
 *     title="通用-產品分類資料列表欄位說明"
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
     * @var Category[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="通用-產品分類資料欄位說明"
 * )
 */
class Category
{
    /**
     * The id of the countries table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 分類名稱
     * @var string
     * @OA\Property(format="string", example="糕餅零食")
     */
    public $name;
    /**
     * 分類簡介
     * @var string
     * @OA\Property(format="string", example="台灣特色美食")
     */
    public $intro;
    /**
     * 分類Logo圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.icarry.me/upload/category/logo_1.jpg")
     */
    public $logo;
    /**
     * 分類封面圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.icarry.me/upload/category/cover_1_1490589933.jpg")
     */
    public $cover;
    /**
     * 排序
     * @var integer
     * @OA\Property(format="float", example="1")
     */
    public $sort;
    /**
     * 商家資料
     * @var CategoryVendors[]
     * @OA\Property()
     */
    public $vendors;
    /**
     * 產品資料
     * @var CategoryProducts[]
     * @OA\Property()
     */
    public $products;
}

/**
 * @OA\Schema(
 *     schema="CategoryVendors",
 *     type="object",
 *     title="通用-產品分類商家資料欄位說明"
 * )
 */
class CategoryVendors
{
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 商家名稱
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $name;
}
/**
 * @OA\Schema(
 *     schema="CategoryProducts",
 *     type="object",
 *     title="通用-產品分類產品資料欄位說明"
 * )
 */
class CategoryProducts
{
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the categories table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $category_id;
    /**
     * 產品名稱
     * @var string
     * @OA\Property(format="string", example="牛軋糖緞帶禮盒(原味)")
     */
    public $name;
}
