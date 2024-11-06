<?php
    /**
     * @OA\Get(
     *     path="/web/v1/product",
     *     operationId="webProductSearch",
     *     tags={"產品"},
     *     summary="前台-搜尋產品列表",
     *     description="1. 輸入關鍵字(keyword 必填)及其他相關參數取得產品資料列表，其餘參數選填。
2. 使用 from_country_id (國家id), to_country_id (國家id) 及 shippingMethod (1:機場提貨 2:旅店提貨 4:指定地點提貨) 參數，提貨地點為台灣時，需提供提貨日期 (pickupDate)。
3. categoryIds 分類參數，請使用陣列方式。
4. lang (en:英文, jp:日文, kr:韓文, th:泰文) 參數用於切換語言時使用。
5. limit 限制筆數、 priceMin (價格範圍最低) 、 priceMax (價格範圍最高)
6. sort 排序，價格：高至低 (priceHighToLow)、價格：低至高 (priceLowToHigh)、人氣最高 (hotest)、最新上架 (latest)，預設：人氣最高
7. 產品列表資料只有商家為啟用狀態且產品狀態為上架中、補貨中。
8. 有輸入 authentication token 時，可取得 is_favorite 欄位 1:使用者最愛商品 0:非使用者最愛商品",
     *     @OA\Parameter(
     *         name="keyword",
     *         description="關鍵字(商家或產品名稱)",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="佳德",
     *     ),
     *     @OA\Parameter(
     *         name="from_country_id",
     *         description="Country id (發貨地)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="0", value="", summary="不選擇"),
     *         @OA\Examples(example="1", value="1", summary="台灣 (參數值 1)"),
     *         @OA\Examples(example="5", value="5", summary="日本 (參數值 5)"),
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="Country id (提貨地)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="shippingMethod",
     *         description="Shipping Method id (1,2,4)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="0", value="", summary="不選擇"),
     *         @OA\Examples(example="1", value="1", summary="機場提貨 (參數值 1)"),
     *         @OA\Examples(example="2", value="2", summary="旅店提貨 (參數值 2)"),
     *         @OA\Examples(example="4", value="4", summary="指定配送 (參數值 4)"),
     *     ),
     *     @OA\Parameter(
     *         name="pickupDate",
     *         description="提貨日(格式:2021-01-01)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="categoryIds[]",
     *         description="分類IDs (使用陣列方式))",
     *         required=false,
     *         in="query",
     *      @OA\Schema(
     *        type="array",
     *        @OA\Items(type="integer"),
     *      )
     *     ),
     *     @OA\Parameter(
     *         name="priceMin",
     *         description="價格範圍 (最低)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="0",
     *     ),
     *     @OA\Parameter(
     *         name="priceMax",
     *         description="價格範圍 (最高)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="4000",
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         description="排序",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="hotest", value="hotest", summary="人氣最高 (參數值 hotest)"),
     *         @OA\Examples(example="latest", value="latest", summary="最新上架 (參數值 latest)"),
     *         @OA\Examples(example="priceHighToLow", value="priceHighToLow", summary="價格：高至低 (參數值 priceHighToLow)"),
     *         @OA\Examples(example="priceLowToHigh", value="priceLowToHigh", summary="價格：低至高 (參數值 priceLowToHigh)"),
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="限制筆數",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(response=200,description="Success, 取得搜尋的產品列表",
     *         @OA\JsonContent(ref="#/components/schemas/ProductSearch")
     *     ),
     *     @OA\Response(response=400, description="Error, appCode 999, 參數錯誤/參數不存在。"),
     *     security={{"webAuth": {}}}
    * )
    */

    /**
     * @OA\Get(
     *     path="/web/v1/product/{id}",
     *     operationId="webProductItem",
     *     tags={"產品"},
     *     summary="前台-取得產品項目",
     *     description="透過 產品id 取得產品項目資料。有輸入 authentication token 時，可取得 is_favorite 欄位 1:使用者最愛商品 0:非使用者最愛商品。",
     *     @OA\Parameter(
     *         name="id",
     *         description="The id of the Product",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="單一款式 2637", value="2637", summary="單一款式 2637"),
     *         @OA\Examples(example="多款式 12674", value="12674", summary="多款式 12674"),
     *         @OA\Examples(example="組合商品 12307", value="12307", summary="組合商品 12307"),
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="Country id (提貨地)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="shippingMethod",
     *         description="Shipping Method id (1,2,4)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="0", value="", summary="不選擇"),
     *         @OA\Examples(example="1", value="1", summary="機場提貨 (參數值 1)"),
     *         @OA\Examples(example="2", value="2", summary="旅店提貨 (參數值 2)"),
     *         @OA\Examples(example="4", value="4", summary="指定配送 (參數值 4)"),
     *     ),
     *     @OA\Parameter(
     *         name="pickupDate",
     *         description="提貨日(格式:2021-01-01)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="產品項目資料, 只有商家啟用及產品狀態為上架中才會顯示",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=400, description="1. Error, appCode 999, 參數錯誤/參數不存在。
2. Error, appCode 0, 商品已下架/不存在。"),
     *     security={{"webAuth": {}}}
     * )
     */

    /**
     * @OA\Get(
     *     path="/web/v1/product/availableDate/{id}",
     *     operationId="webProductAvailableDate",
     *     deprecated=true,
     *     tags={"產品"},
     *     summary="前台-取得產品最快可提貨日期",
     *     description="透過 product_model_id 取得產品最快可提貨日期，此功能僅適用於寄送國家為台灣。",
     *     @OA\Parameter(
     *         name="id",
     *         description="The id of the Product Model",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         example="10974",
     *     ),
     *     @OA\Parameter(
     *         name="shippingMethod",
     *         description="1 機場提貨 2 旅店提貨 (指定地點)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="機場提貨", value="1", summary="機場提貨"),
     *         @OA\Examples(example="旅店提貨", value="2", summary="旅店提貨"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="取得產品最快提貨日",
     *     ),
     *     @OA\Response(response=404,description="資料不存在。"),
     * )
     */

    /**
     * @OA\Get(
     *     path="/web/v1/product/allowCountry/{id}",
     *     operationId="webProductAllowCountry",
     *     tags={"產品"},
     *     summary="前台-取得產品可寄送國家",
     *     description="透過 product_model_id 取得產品可寄送國家id，以逗號分隔",
     *     @OA\Parameter(
     *         name="id",
     *         description="The id of the Product Model",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         example="10974",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="取得產品可寄送國家id",
     *     ),
     *     @OA\Response(response=404,description="資料不存在。"),
     * )
     */
    /**
     * @OA\Get(
     *     path="/web/v1/category",
     *     operationId="webCategoryList",
     *     tags={"產品"},
     *     summary="前台-產品分類資料列表",
     *     description="1. 未輸入任何參數只會取得已啟用的產品分類資料。
2. 輸入 type 參數可取得附加資料， type=vendor 可取得相關商家名稱與ID資料， type=product 可取得相關產品名稱與ID資料
3. 輸入 lang 參數可取得相對應語言資料，相對應語言找不到時，以英文優先，若也無英文資料則改為中文。
4. type=product 產品資料較多取得速度較慢，請將資料放置於暫存區或cookie中，避免頻繁連線。",
     *     @OA\Parameter(
     *         name="type",
     *         description="類型 (vendor or product)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="類型 - 不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="類型 - vendor", value="vendor", summary="類型 - vendor"),
     *         @OA\Examples(example="類型 - product", value="product", summary="類型 - product"),
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得產品分類資料列表",
     *         @OA\JsonContent(ref="#/components/schemas/CategorySuccess")
     *     ),
     *     @OA\Response(response=400,description="Failure, 參數不存在/參數錯誤"),
    * )
    */
