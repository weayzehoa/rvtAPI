<?php
/**
 * @OA\Schema(
 *     schema="CurationSuccess",
 *     type="object",
 *     title="前台-策展清單列表欄位說明"
 * )
 */
class CurationSuccess
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
     * @var Curation[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="curation",
 *     type="object",
 *     title="前台-策展欄位說明"
 * )
 */
class Curation
{
    /**
     * The id of the curation table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * 策展類別, 首頁策展 = home, 分類策展 = category
     * @var string
     * @OA\Property(format="string", example="home")
     */
    public $category;
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
     * 大標題背景顏色
     * @var string
     * @OA\Property(format="string", example="#FF00FFFF")
     */
    public $main_title_background;
    /**
     * 大標題背景顏色顯示開關, 1:開 0:關
     * @var boolean
     * @OA\Property(format="boolean", example="0")
     */
    public $show_main_title_background;
    /**
     * 策展版型背景顏色
     * @var string
     * @OA\Property(format="string", example="#CCCCCCFF")
     */
    public $background_color;
    /**
     * 策展版型自訂背景CSS
     * @var boolean
     * @OA\Property(format="boolean", example="background-color: #00ffff;")
     */
    public $background_css;
    /**
     * 策展版型背景圖片
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw/upload/curation/background_image_1_1620880149.png")
     */
    public $background_image;
    /**
     * 策展版型背景類型顯示開關, off:不顯示 color:顯示顏色 css:顯示自訂CSS
     * @var string
     * @OA\Property(format="string", example="off")
     */
    public $show_background_type;
    /**
     * 策展版型欄數 1-6
     * @var integer
     * @OA\Property(format="integer", example="4")
     */
    public $columns;
    /**
     * 策展版型列數 1-2 (宮格版型用)
     * @var integer
     * @OA\Property(format="integer", example="2")
     */
    public $rows;
    /**
     * 說明簡介 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="說明簡介....")
     */
    public $caption;
    /**
     * 版型種類 (標頭 header, 圖片 image, 活動 event, 宮格 block, 宮格無字 nowordBlock, 品牌 vendor, 產品 product)
     * @var string
     * @OA\Property(format="string", example="product")
     */
    public $type;
    /**
     * 連結
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw")
     */
    public $url;
    /**
     * 連結另開視窗
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $url_open_window;
    /**
     * 連結顯示開關, 1:開 0:關
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $show_url;
    /**
     * 策展開始時間
     * @var string
     * @OA\Property(format="datetime", example="2020-04-17 00:00:30")
     */
    public $start_time;
    /**
     * 策展結束時間
     * @var string
     * @OA\Property(format="datetime", example="2099-05-11 00:00:30")
     */
    public $end_time;
    /**
     * 版型開關, 1:開 0:關
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $is_on;
    /**
     * 版型排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
    /**
     * 產品資料 (type = product 出現)
     * @var CurationProducts[]
     * @OA\Property()
     */
    public $products;
    /**
     * 品牌資料 (type = vendor 出現)
     * @var CurationVendors[]
     * @OA\Property()
     */
    public $vendors;
    /**
     * 圖片版型資料 (type = image 出現)
     * @var CurationImages[]
     * @OA\Property()
     */
    public $images;
    /**
     * 宮格版型資料 (type = block 出現)
     * @var CurationImages[]
     * @OA\Property()
     */
    public $blocks;
    /**
     * 宮格(無字)版型資料 (type = nowordBlock 出現)
     * @var CurationnowordBlocks[]
     * @OA\Property()
     */
    public $nowordBlocks;
    /**
     * 活動版型資料 (type = event 出現)
     * @var CurationImages[]
     * @OA\Property()
     */
    public $events;
}

/**
 * @OA\Schema(
 *     schema="CurationProducts",
 *     type="object",
 *     title="前台-策展-產品版型欄位說明"
 * )
 */
class CurationProducts
{
    /**
     * The id of the curation_products table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the products table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $product_id;
    /**
     * The id of the curation table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $curation_id;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="過海製麵所")
     */
    public $vendor_name;
    /**
     * 產品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="曾拌麵-胡蔴醬香(細麵，全素)")
     */
    public $name;
    /**
     * 上標文字 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="Jei De")
     */
    public $curation_text_top;
    /**
     * 下標文字 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="Top 10 Brands")
     */
    public $curation_text_bottom;
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
     * 產品排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
    /**
     * 產品狀態 (只會出現 1 上架中, 其餘排除)
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $status;
    /**
     * 是否為最愛商品 (1:是 0:否) (有登入驗證才出現)
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $is_favorite;
    /**
     * 圖片資料
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw/upload/Product/photo_48_1572847556.jpg")
     */
    public $image;
}

/**
 * @OA\Schema(
 *     schema="CurationVendors",
 *     type="object",
 *     title="前台-策展-品牌版型欄位說明"
 * )
 */
class CurationVendors
{
    /**
     * The id of the curation_vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $vendor_id;
    /**
     * The id of the curation table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $curation_id;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="過海製麵所")
     */
    public $name;
    /**
     * 簡介文字 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="曾拌麵-胡蔴醬香")
     */
    public $curation;
    /**
     * 品牌排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
    /**
     * 商家狀態 (只會出現 1 啟用, 其餘排除)
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $status;
}

/**
 * @OA\Schema(
 *     schema="CurationImages",
 *     type="object",
 *     title="前台-策展-圖片、活動或宮格版型欄位說明"
 * )
 */
class CurationImages
{
    /**
     * The id of the curation_images table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the curation table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $curation_id;
    /**
     * 圖片版型所屬類別(image, block, event, nowordBlock)
     * @var string
     * @OA\Property(format="string", example="image")
     */
    public $style;
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
     * 文字位置, inside 圖片內部, bottom 圖片底部
     * @var string
     * @OA\Property(format="string", example="inside")
     */
    public $text_position;
    /**
     * 連結
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw")
     */
    public $url;
    /**
     * 連結另開視窗
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $url_open_window;
    /**
     * 開啟方式(連結或Modal) (僅限圖片版型)
     * @var integer
     * @OA\Property(format="string", example="url")
     */
    public $open_method;
    /**
     * Modal內容 (僅限圖片版型)
     * @var integer
     * @OA\Property(format="text", example="預購訂單滿額回饋 5%")
     */
    public $modal_content;
    /**
     * 策展版型背景圖片
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw/upload/curation/photo_48_1572847556.jpg")
     */
    public $image;
    /**
     * 圖片排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
}

/**
 * @OA\Schema(
 *     schema="CurationnowordBlocks",
 *     type="object",
 *     title="前台-策展-宮格(無字)版型欄位說明"
 * )
 */
class CurationnowordBlocks
{
    /**
     * The id of the curation_images table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the curation table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $curation_id;
    /**
     * 圖片版型所屬類別(nowordBlock)
     * @var string
     * @OA\Property(format="string", example="image")
     */
    public $style;
    /**
     * 連結
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw")
     */
    public $url;
    /**
     * 策展版型背景圖片
     * @var string
     * @OA\Property(format="string", example="https://api.rvt.idv.tw/upload/curation/photo_48_1572847556.jpg")
     */
    public $image;
    /**
     * 圖片排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
}
