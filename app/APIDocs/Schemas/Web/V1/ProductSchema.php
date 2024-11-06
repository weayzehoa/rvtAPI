<?php
/**
 * @OA\Schema(
 *     schema="ProductSearch",
 *     type="object",
 *     title="前台-產品搜尋欄位說明"
 * )
 */
class ProductSearch
{
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="success")
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
     * @var SearchKeyword[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="SearchKeyword",
 *     type="object",
 *     title="前台-關鍵字搜尋產品欄位說明"
 * )
 */
class SearchKeyword
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
     * The id of the Vendors table
     * @var integer
     * @OA\Property(format="int64", example=20)
     */
    public $vendor_id;
    /**
     * 品牌名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $vendor_name;
    /**
     * 熱門程度 (值越大越熱門)
     * @var string
     * @OA\Property(format="string", example="13235")
     */
    public $hotest;
    /**
     * 商品售價
     * @var integer
     * @OA\Property(format="int64", example=120)
     */
    public $price;
    /**
     * 商品建議售價
     * @var integer
     * @OA\Property(format="int64", example=200)
     */
    public $fake_price;
    /**
     * 不支援寄送至選擇的國家 (1:是, 0:否) (有參數時出現) (0 則不顯示訊息)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $canNotSend;
    /**
     * 商品圖片1
     * @var string
     * @OA\Property(format="string", example="https://cdn.icarry.me/upload/product/new_photo1_2646_1571824006.jpg")
     */
    public $image1;
    /**
     * 商品圖片2
     * @var string
     * @OA\Property(format="string", example="https://cdn.icarry.me/upload/product/new_photo1_2646_1571824006.jpg")
     */
    public $image2;
    /**
     * 商品圖片3
     * @var string
     * @OA\Property(format="string", example="https://cdn.icarry.me/upload/product/new_photo1_2646_1571824006.jpg")
     */
    public $image3;
    /**
     * 商品圖片4
     * @var string
     * @OA\Property(format="string", example="https://cdn.icarry.me/upload/product/new_photo1_2646_1571824006.jpg")
     */
    public $image4;
    /**
     * 商品圖片5
     * @var string
     * @OA\Property(format="string", example="https://cdn.icarry.me/upload/product/new_photo1_2646_1571824006.jpg")
     */
    public $image5;
    /**
     * 庫存不足 (1:是, 0:否) (有參數時出現) (0 則不顯示訊息)
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
}

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="前台-產品欄位說明"
 * )
 */
class Product
{
    /**
     * The id of the Products table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
    /**
     * The id of the Vendors table
     * @var integer
     * @OA\Property(format="int64", example=20)
     */
    public $vendor_id;
    /**
     * 產品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="原味鳳梨酥禮盒(12入)")
     */
    public $name;
    /**
     * 品牌名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $brand;
    /**
     * 商品售價
     * @var integer
     * @OA\Property(format="int64", example=120)
     */
    public $price;
    /**
     * 商品建議售價 (0 則不顯示)
     * @var integer
     * @OA\Property(format="int64", example=200)
     */
    public $fake_price;
    /**
     * 包裝內容 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="一盒12顆，每顆約45克")
     */
    public $serving_size;
    /**
     * 產品特色 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="大小適中")
     */
    public $title;
    /**
     * 商品簡介 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="奶蛋素。台灣最佳伴手禮，一吃就上癮。")
     */
    public $intro;
    /**
     * 款式名稱 (多款式產品顯示) (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="顏色尺寸")
     */
    public $model_name;
    /**
     * 淨重(g)
     * @var integer
     * @OA\Property(format="integer", example="125")
     */
    public $net_weight;
    /**
     * 毛重(g)
     * @var integer
     * @OA\Property(format="integer", example="300")
     */
    public $gross_weight;
    /**
     * 保存期限(天)
     * @var integer
     * @OA\Property(format="integer", example="30")
     */
    public $storage_life;
    /**
     * 是否為最愛商品 (1:是 0:否) (有登入驗證才出現)
     * @var integer
     * @OA\Property(format="integer", example="1")
     */
    public $is_favorite;
    /**
     * 產品詳細規格 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="text", example="html DATA")
     */
    public $specification;
    /**
     * 商品編號
     * @var string
     * @OA\Property(format="string", example="EC00020002469")
     */
     public $sku;
    /**
     * 商品庫存 (model_type = 1 時顯示)
     * @var string
     * @OA\Property(format="string", example="10")
     */
     public $quantity;
    /**
     * 款式 (1:單一商品 2:多款商品 3:組合商品)
     * @var integer
     * @OA\Property(format="integer", example="3")
     */
    public $model_type;
    /**
     * product_model_id (model_type = 1 才有值)
     * @var integer
     * @OA\Property(format="integer", example="2637")
     */
    public $product_model_id;
    /**
     * 商品狀態 (-3:補貨中 = 庫存不足, 加入購物車及即立結帳按鈕 改為 庫存不足按鈕)
     * @var integer
     * @OA\Property(format="integer", example="-3")
     */
    public $status;
    /**
     * 審核通過時間(最新上架)
     * @var integer
     * @OA\Property(format="string", example="2021-12-30 12:34:56")
     */
    public $pass_time;
    /**
     * 付款後多少工作天寄出
     * @var integer
     * @OA\Property(format="integer", example="3")
     */
    public $shipping_days_after_paid;
    /**
     * 不支援寄送至選擇的國家 (1:是, 0:否) (有參數時出現) (0 則不顯示訊息, 1則顯示訊息並禁用加入購物車及立即購買)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $canNotSend;
    /**
     * 庫存不足 (1:是, 0:否) (有參數時出現) (0 則不顯示訊息, 1則顯示訊息並禁用加入購物車及立即購買)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $outOffStock;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $vendor_name;
    /**
     * 商家簡介 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="2012榮獲第三屆「台灣100大觀光特產認證」")
     */
    public $vendor_summary;
    /**
     * 商家描述 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅有限公司創立於1975年，從創立佳德糕餅至今...")
     */
    public $vendor_description;
    /**
     * 無法購買理由 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="缺貨")
     */
    public $unable_buy;
    /**
     * 多款商品資料 (model_type = 2 出現)
     * @var ProductStyles[]
     * @OA\Property()
     */
    public $styles;
    /**
     * 組合商品資料 (mode_type = 3 出現)
     * @var ProductPackages[]
     * @OA\Property()
     */
    public $packages;
    /**
     * 商家人氣商品
     * @var VendorHotProducts[]
     * @OA\Property()
     */
    public $vendor_hot_products;
}

/**
 * @OA\Schema(
 *     schema="ProductStyles",
 *     type="object",
 *     title="前台-產品-多款商品欄位說明"
 * )
 */
class ProductStyles
{
    /**
     * The id of the product_models table
     * @var integer
     * @OA\Property(format="int64", example=2324)
     */
    public $product_model_id;
    /**
     * 多款商品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="鼠來報吉「三星蔥口味/鹹蛋黃口味」雙盒 (32入/2盒)")
     */
    public $name;
    /**
     * 商品編號
     * @var string
     * @OA\Property(format="string", example="EC00410012028")
     */
    public $sku;
    /**
     * 商品庫存
     * @var string
     * @OA\Property(format="string", example="10")
     */
    public $quantity;
    /**
     * 庫存不足 (1:是, 0:否) (1則禁用按鈕)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $outOffStock;
}

/**
 * @OA\Schema(
 *     schema="ProductPackages",
 *     type="object",
 *     title="前台-產品-組合商品欄位說明"
 * )
 */
class ProductPackages
{
    /**
     * The id of the product_models table
     * @var integer
     * @OA\Property(format="int64", example=2324)
     */
    public $product_model_id;
    /**
     * 組合商品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="榴槤杏仁牛軋糖")
     */
    public $name;
    /**
     * 商品編號
     * @var string
     * @OA\Property(format="string", example="BOM005271608772413")
     */
    public $sku;
    /**
     * 商品庫存
     * @var string
     * @OA\Property(format="string", example="10")
     */
    public $quantity;
    /**
     * 庫存不足 (1:是, 0:否) (1則禁用按鈕)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $outOffStock;
}

/**
 * @OA\Schema(
 *     schema="ProductVendorLangs",
 *     type="object",
 *     title="前台-產品-商家語言欄位說明"
 * )
 */
class ProductVendorLangs
{
    /**
     * The id of the vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $vendor_id;
    /**
     * 語言代號 (en,jp,kr,th)
     * @var string
     * @OA\Property(format="string", example="en")
     */
    public $lang;
    /**
     * 商家名稱
     * @var string
     * @OA\Property(format="string", example="Jei De")
     */
    public $name;
    /**
     * 簡介
     * @var string
     * @OA\Property(format="text", example="The bakery's winning entry ...")
     */
    public $summary;
    /**
     * 描述
     * @var string
     * @OA\Property(format="longtext", example="The bakery's winning entry ...")
     */
    public $description;
}

/**
 * @OA\Schema(
 *     schema="ProductImages",
 *     type="object",
 *     title="前台-產品-多張圖片欄位說明"
 * )
 */
class ProductImages
{
    /**
     * 商品圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.icarry.me/upload/Product/photo_48_1572847556.jpg")
     */
    public $filename;
    /**
     * 圖片排序
     * @var float
     * @OA\Property(format="float", example="1")
     */
    public $sort;
}

/**
 * @OA\Schema(
 *     schema="vendorHotProducts",
 *     type="object",
 *     title="前台-產品-商家人氣商品欄位說明"
 * )
 */
class vendorHotProducts
{
    /**
     * The id of the Vendors table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $vendor_id;
    /**
     * The id of the Products table
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $Product_id;
    /**
     * 商家名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $vendor_name;
    /**
     * 商品名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="經典蜂蜜蛋糕 x 2盒")
     */
    public $product_name;
    /**
     * 商品售價
     * @var integer
     * @OA\Property(format="int64", example=120)
     */
    public $price;
    /**
     * 商品建議售價
     * @var integer
     * @OA\Property(format="int64", example=200)
     */
    public $fake_price;
    /**
     * 商品圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.icarry.me/upload/Product/photo_48_1572847556.jpg")
     */
    public $image;
}
