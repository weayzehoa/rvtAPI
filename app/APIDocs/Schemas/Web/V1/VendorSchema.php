<?php
/**
 * @OA\Schema(
 *     schema="Vendor",
 *     type="object",
 *     title="前台-商家資料欄位說明"
 * )
 */
class Vendor
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
     * @var VendorData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="VendorData",
 *     type="object",
 *     title="前台-商家單頁資料欄位說明"
 * )
 */
class VendorData
{
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $name;
    /**
     * 商家Logo圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/oldpic/vendor/logo_236.png")
     */
    public $img_logo;
    /**
     * 商家主視覺圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/upload/vendor/site_cover_20_1491487607.png")
     */
    public $img_cover;
    /**
     * 商家網站滿版圖
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/upload/vendor/site_cover_20_1491487607.png")
     */
    public $img_site;
    /**
     * 商家描述 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅有限公司創立於1975年...")
     */
    public $description;
    /**
     * 產品資料
     * @var ProductsData[]
     * @OA\Property()
     */
    public $products_data;
    /**
     * 策展資料
     * @var CurationsData[]
     * @OA\Property()
     */
    public $curations;
}

/**
 * @OA\Schema(
 *     schema="ProductsData",
 *     type="object",
 *     title="前台-商家資料-產品欄位說明"
 * )
 */
class ProductsData
{
    /**
     * The id of the Products table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 產品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="原味鳳梨酥禮盒(12入)")
     */
    public $name;
    /**
     * 上標文字 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="超好吃")
     */
    public $curation_text_top;
    /**
     * 上標文字 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="超好吃")
     */
    public $curation_text_bottom;
    /**
     * 熱門程度
     * @var integer
     * @OA\Property(format="integer", example="168")
     */
    public $hotest;
    /**
     * 建議售價
     * @var integer
     * @OA\Property(format="integer", example="168")
     */
    public $fake_price;
    /**
     * 售價
     * @var integer
     * @OA\Property(format="integer", example="134")
     */
    public $price;
    /**
     * 庫存不足 (1:是, 0:否) (1則顯示)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $outOffStock;
    /**
     * 是否為最愛商品 (1:是 0:否) (有登入驗證才出現)
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $is_favorite;
    /**
     * 圖片資料
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/upload/product/photo1_3654_1564738620.png://dev-cdn.rvt.idv.tw/upload/product/photo1_3654_1564738620.png34")
     */
    public $image;
}

/**
 * @OA\Schema(
 *     schema="CurationsData",
 *     type="object",
 *     title="前台-商家資料-策展欄位說明"
 * )
 */
class CurationsData
{
    /**
     * The id of the Curations table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the Vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $vendor_id;
    /**
     * 大標題 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="最受歡迎的台灣伴手禮品牌")
     */
    public $main_title;
    /**
     * 大標題顯示開關, 1:開 0:關
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $show_main_title;
    /**
     * 小標題 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="台灣必買十大伴手禮精選")
     */
    public $sub_title;
    /**
     * 小標題顯示開關, 1:開 0:關
     * @var boolean
     * @OA\Property(format="boolean", example="0")
     */
    public $show_sub_title;
    /**
     * 產品資料 (type = product 出現)
     * @var CurationProducts[]
     * @OA\Property()
     */
    public $products;
}
