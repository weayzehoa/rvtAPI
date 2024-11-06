<?php
/**
 * @OA\Schema(
 *     schema="shoppingCartTotalSuccess",
 *     type="object",
 *     title="前台-使用者購物車數量欄位說明"
 * )
 */
class shoppingCartTotalSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="購物車數量")
     */
    public $message;
    /**
     * 購物車數量
     * @var integer
     * @OA\Property(format="integer", example="5")
     */
    public $data;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartAmountSuccess",
 *     type="object",
 *     title="前台-使用者購物車計算可結帳購物車資料欄位說明"
 * )
 */
class shoppingCartAmountSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="計算完成")
     */
    public $message;
    /**
     * 資料內容
     * @var shippingCartCalculation[]
     * @OA\Property()
     */
    public $data;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartAmountUserData",
 *     type="object",
 *     title="前台-使用者購物車計算可結帳購物車資料內容使用者資料欄位說明"
 * )
 */
class shoppingCartAmountUserData
{
    /**
     * 使用者 id
     * @var integer
     * @OA\Property(format="int64", example="1234")
     */
    public $id;
    /**
     * 名字
     * @var string
     * @OA\Property(format="string", example="roger Me")
     */
    public $name;
    /**
     * 國際碼
     * @var string
     * @OA\Property(format="string", example="+886")
     */
    public $nation;
    /**
     * 電話號碼
     * @var integer
     * @OA\Property(format="integer", example="123456789")
     */
    public $mobile;
    /**
     * 電子郵件
     * @var string
     * @OA\Property(format="string", example="roger@rvt.idv.tw")
     */
    public $email;
    /**
     * 綁定信用卡資料
     * @var string
     * @OA\Property(format="string", example="12**-****-****-1234")
     */
    public $cardlink;
    /**
     * 亞洲萬里通帳號
     * @var integer
     * @OA\Property(format="integer", example="1234567890")
     */
    public $asiamiles_account;
    /**
     * 其他聯絡資訊
     * @var string
     * @OA\Property(format="string", example="line:roger")
     */
    public $other_contact;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartAmountUserAddress",
 *     type="object",
 *     title="前台-使用者購物車計算可結帳購物車資料內容使用者常用地址欄位說明"
 * )
 */
class shoppingCartAmountUserAddress
{
    /**
     * 使用者常用地址 id (user_address_id)
     * @var integer
     * @OA\Property(format="int64", example="1234")
     */
    public $id;
    /**
     * 名字
     * @var string
     * @OA\Property(format="string", example="roger Me")
     */
    public $name;
    /**
     * 國際碼
     * @var string
     * @OA\Property(format="string", example="+86")
     */
    public $nation;
    /**
     * 電話號碼
     * @var integer
     * @OA\Property(format="integer", example="123456789")
     */
    public $phone;
    /**
     * 國家名稱
     * @var string
     * @OA\Property(format="string", example="中國")
     */
    public $country;
    /**
     * 省
     * @var string
     * @OA\Property(format="string", example="廣東省")
     */
    public $province;
    /**
     * 市
     * @var string
     * @OA\Property(format="string", example="廣州市")
     */
    public $city;
    /**
     * 區
     * @var string
     * @OA\Property(format="string", example="北區")
     */
    public $area;
    /**
     * 地址
     * @var string
     * @OA\Property(format="string", example="南京東路")
     */
    public $address;
    /**
     * Zip Code
     * @var string
     * @OA\Property(format="string", example="12345")
     */
    public $zip_code;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartApplySuccess",
 *     type="object",
 *     title="前台-使用者購物車套用或取消(促銷代碼/購物金)欄位說明"
 * )
 */
class shoppingCartApplySuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="套用促銷代碼成功")
     */
    public $message;
    /**
     * 資料內容
     * @var shippingCartApplyData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="shippingCartApplyData",
 *     type="object",
 *     title="前台-使用者購物車套用/取消(促銷代碼/購物金)重新計算欄位說明"
 * )
 */
class shippingCartApplyData
{
    /**
     * 重新計算資料
     * @var shippingCartCalculation[]
     * @OA\Property()
     */
    public $reCalculation;
}

/**
 * @OA\Schema(
 *     schema="shoppingCartSuccess",
 *     type="object",
 *     title="前台-使用者購物車列表欄位說明"
 * )
 */
class shoppingCartSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息 (可結帳、無法結帳統計)
     * @var shoppingCartTotal[]
     * @OA\Property()
     */
    public $message;
    /**
     * 資料內容
     * @var shippingCartListData[]
     * @OA\Property()
     */
    public $data;
}

/**
 * @OA\Schema(
 *     schema="shoppingCartTotal",
 *     type="object",
 *     title="前台-使用者購物車(可結帳、無法結帳統計)欄位說明"
 * )
 */
class shoppingCartTotal
{
    /**
     * 可結帳筆數
     * @var integer
     * @OA\Property(format="integer", example="4")
     */
    public $availableTotal;
    /**
     * 無法結帳筆數
     * @var integer
     * @OA\Property(format="integer", example="14")
     */
    public $unAvailableTotal;
}

/**
 * @OA\Schema(
 *     schema="shippingCartListData",
 *     type="object",
 *     title="前台-使用者購物車列表資料欄位說明"
 * )
 */
class shippingCartListData
{
    /**
     * 計算金額及相關文字資料
     * @var shippingCartCalculation[]
     * @OA\Property()
     */
    public $calculation;
    /**
     * 可結帳資料
     * @var shippingCartData[]
     * @OA\Property()
     */
    public $available;
    /**
     * 無法結帳資料
     * @var shippingCartData[]
     * @OA\Property()
     */
    public $unAvailable;
}

/**
 * @OA\Schema(
 *     schema="shippingCartCalculation",
 *     type="object",
 *     title="前台-使用者購物車列表計算金額及相關資料欄位說明"
 * )
 */
class shippingCartCalculation
{
    /**
     * 可結帳商品數量
     * @var integer
     * @OA\Property(format="integer", example="3")
     */
    public $availableQuantity;
    /**
     * 商品總計金額
     * @var integer
     * @OA\Property(format="integer", example="2588")
     */
    public $productAmount;
    /**
     * 跨境稅 (根據地區不同計算)
     * @var integer
     * @OA\Property(format="integer", example="336")
     */
    public $parcelTax;
    /**
     * 跨境稅稅率(%) (根據地區不同計算)
     * @var integer
     * @OA\Property(format="integer", example="13.00")
     */
    public $parcelTaxRate;
    /**
     * 免運費門檻(NT)
     * @var integer
     * @OA\Property(format="integer", example="1800")
     */
    public $freeShipping;
    /**
     * 運費金額 (根據地區不同計算)
     * @var integer
     * @OA\Property(format="integer", example="240")
     */
    public $shippingFee;
    /**
     * 每公斤運費
     * @var integer
     * @OA\Property(format="integer", example="200")
     */
    public $shippingKgFee;
    /**
     * 促銷折抵金額 (負數)
     * @var integer
     * @OA\Property(format="integer", example="-150")
     */
    public $discount;
    /**
     * 購物金折抵金額 (負數)
     * @var integer
     * @OA\Property(format="integer", example="-100")
     */
    public $points;
    /**
     * 總金額 (商品 + 運費 + 跨境稅 + 促銷折抵 + 購物金折抵)
     * @var integer
     * @OA\Property(format="integer", example="4103")
     */
    public $totalAmount;
    /**
     * 總重量(kg)
     * @var integer
     * @OA\Property(format="integer", example="1.06")
     */
    public $totalGrossWeight;
}

/**
 * @OA\Schema(
 *     schema="shippingCartData",
 *     type="object",
 *     title="前台-使用者購物車列表商品資料欄位說明"
 * )
 */
class shippingCartData
{
    /**
     * The id of the shopping_carts table (購物車id,更改數量及刪除用)
     * @var integer
     * @OA\Property(format="int64", example="123")
     */
    public $id;
    /**
     * The id of the products table (商品id,商品連結用)
     * @var integer
     * @OA\Property(format="int64", example="1234")
     */
    public $product_id;
    /**
     * The id of the product_models table (新增訂單帶入此id)
     * @var integer
     * @OA\Property(format="int64", example="4567")
     */
    public $product_mode_id;
    /**
     * 商品款式 (1:單一款式 2:多款式 3:組合商品)
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $mode_type;
    /**
     * 商品款式名稱 (model_type = 1 則為空值不顯示) (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="黑糖口味")
     */
    public $model_name;
    /**
     * 商品單價
     * @var integer
     * @OA\Property(format="int64", example="123")
     */
    public $price;
    /**
     * 數量
     * @var integer
     * @OA\Property(format="int64", example="2")
     */
    public $quantity;
    /**
     * 商品小計 (數量 x 單價)
     * @var integer
     * @OA\Property(format="int64", example="246")
     */
    public $amount_price;
    /**
     * 庫存不足 (1:是, 0:否) (關閉更改數量)
     * @var integer
     * @OA\Property(format="int64", example="1")
     */
    public $outOffStock;
    /**
     * 可否結帳 (1:是, 0:否) (0 新增訂單不帶入)
     * @var integer
     * @OA\Property(format="int64", example="0")
     */
    public $available;
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
    public $product_name;
    /**
     * 無法結帳理由 (除了中文外，其餘語言只有英文)
     * @var string
     * @OA\Property(format="string", example="庫存不足")
     */
    public $unAvailableReason;
}


/**
 * @OA\Schema(
 *     schema="shoppingCartCreateSuccess",
 *     type="object",
 *     title="前台-使用者購物車新增成功資料欄位說明"
 * )
 */
class shoppingCartCreateSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息 (可結帳、無法結帳統計)
     * @var string
     * @OA\Property(format="string", example="新增成功")
     */
    public $message;
    /**
     * 資料內容
     * @var shippingCartCreateData[]
     * @OA\Property()
     */
    public $data;
        /**
     * httpCode (http回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="200")
     */
    public $httpCode;
}


/**
 * @OA\Schema(
 *     schema="shippingCartCreateData",
 *     type="object",
 *     title="前台-使用者購物車新增資料欄位說明"
 * )
 */
class shippingCartCreateData
{
    /**
     * 購物車id
     * @var integer
     * @OA\Property(format="integer", example="123456")
     */
    public $id;
    /**
     * Product Model ID (新增訂單用id)
     * @var integer
     * @OA\Property(format="integer", example="2021")
     */
    public $product_model_id;
    /**
     * 數量
     * @var integer
     * @OA\Property(format="integer", example="2")
     */
    public $quantity;
}

/**
 * @OA\Schema(
 *     schema="shoppingCartUpdateSuccess",
 *     type="object",
 *     title="前台-使用者購物車修改成功資料欄位說明"
 * )
 */
class shoppingCartUpdateSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="修改成功")
     */
    public $message;
    /**
     * 資料內容
     * @var shippingCartUpdateData[]
     * @OA\Property()
     */
    public $data;
        /**
     * httpCode (http回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="200")
     */
    public $httpCode;
}

/**
 * @OA\Schema(
 *     schema="shippingCartUpdateData",
 *     type="object",
 *     title="前台-使用者購物車修改資料欄位說明"
 * )
 */
class shippingCartUpdateData
{
    /**
     * 購物車id
     * @var integer
     * @OA\Property(format="integer", example="123456")
     */
    public $id;
    /**
     * 使用者id
     * @var integer
     * @OA\Property(format="integer", example="12345")
     */
    public $user_id;
    /**
     * Product Model ID (新增訂單用id)
     * @var integer
     * @OA\Property(format="integer", example="2021")
     */
    public $product_model_id;
    /**
     * 客戶端 uuid
     * @var string
     * @OA\Property(format="string", example="72c1a308-154f-4867-94ad-963bda03f93d")
     */
    public $session;
    /**
     * 網域
     * @var string
     * @OA\Property(format="string", example="rvt.idv.tw")
     */
    public $domain;
}

/**
 * @OA\Schema(
 *     schema="shoppingCartShowSuccess",
 *     type="object",
 *     title="前台-使用者購物車資料欄位說明"
 * )
 */
class shoppingCartShowSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * appCode (api回應代碼)
     * @var integer
     * @OA\Property(format="integer", example="0")
     */
    public $appCode;
    /**
     * 回應訊息
     * @var string
     * @OA\Property(format="string", example="購物車資料")
     */
    public $message;
    /**
     * 資料內容
     * @var shoppingCartShowData[]
     * @OA\Property()
     */
    public $data;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartShowData",
 *     type="object",
 *     title="前台-使用者購物車資料內容欄位說明"
 * )
 */
class shoppingCartShowData
{
    /**
     * The id of the shopping_carts table
     * @var integer
     * @OA\Property(format="int64", example="123")
     */
    public $id;
    /**
     * The id of the products table (商品id,商品連結用)
     * @var integer
     * @OA\Property(format="int64", example="1234")
     */
    public $product_id;
    /**
     * The id of the product_models table (新增訂單帶入此id)
     * @var integer
     * @OA\Property(format="int64", example="4567")
     */
    public $product_mode_id;
    /**
     * 商品款式 (1:單一款式 2:多款式 3:組合商品)
     * @var boolean
     * @OA\Property(format="boolean", example="1")
     */
    public $mode_type;
    /**
     * 商品款式名稱 (model_type = 1 則為空值不顯示) (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="黑糖口味")
     */
    public $model_name;
    /**
     * 商品單價
     * @var integer
     * @OA\Property(format="int64", example="123")
     */
    public $price;
    /**
     * 目前數量
     * @var integer
     * @OA\Property(format="int64", example="2")
     */
    public $quantity;
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
    public $product_name;
    /**
     * 商品圖片
     * @var string
     * @OA\Property(format="string", example="https://dev-cdn.rvt.idv.tw/upload/Product/photo_48_1572847556.jpg")
     */
    public $images;
    /**
     * 款式資料內容 (model_type = 1 不出現)
     * @var shoppingCartShowDataModels[]
     * @OA\Property()
     */
    public $product_models;
}
/**
 * @OA\Schema(
 *     schema="shoppingCartShowDataModels",
 *     type="object",
 *     title="前台-使用者購物車資料內容-款式資料內容欄位說明"
 * )
 */
class shoppingCartShowData
{
    /**
     * The id of the product_models table (修改時帶入此id)
     * @var integer
     * @OA\Property(format="int64", example="4567")
     */
    public $product_mode_id;
    /**
     * 名稱 (根據語言輸入顯示不同語言資料，若無則以中文替代)
     * @var string
     * @OA\Property(format="string", example="佳德糕餅")
     */
    public $name;
    /**
     * 庫存不足 (1:是, 0:否) (1 則關閉選擇)
     * @var integer
     * @OA\Property(format="int1", example=0)
     */
    public $outOffStock;
}
