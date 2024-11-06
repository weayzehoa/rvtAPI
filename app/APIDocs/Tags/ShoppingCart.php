<?php
    /**
     * @OA\Get(
     *     path="/web/v1/shoppingCart/total",
     *     operationId="webUserShoppingCartTotal",
     *     tags={"購物車"},
     *     summary="購物車數量",
     *     description="取得購物車數量，
1. 未登入取得該瀏覽器購物車數量。
2. 有登入則取得該瀏覽器購物車資料及使用者購物車數量。",
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得可結帳購物車數量。",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartTotalSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode = 999 參數不存在/參數錯誤。",
     *     ),
     *     security={{"webAuth": {}}}
     * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/shoppingCart/amount",
     *     operationId="webUserShoppingCartAmount",
     *     tags={"購物車"},
     *     summary="計算可結帳購物車資料",
     *     description="計算可結帳購物車資料",
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Parameter(
     *         name="from_country_id",
     *         description="發貨地區國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣發貨", value="1", summary="台灣發貨"),
     *         @OA\Examples(example="日本發貨", value="5", summary="日本發貨"),
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="提貨地點國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣", value="1", summary="台灣"),
     *         @OA\Examples(example="中國", value="2", summary="中國"),
     *         @OA\Examples(example="日本", value="5", summary="日本"),
     *     ),
     *     @OA\Parameter(
     *         name="shipping_method_id",
     *         description="物流方式id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="寄送海外/指定地點", value="4", summary="寄送海外/指定地點"),
     *         @OA\Examples(example="機場提貨", value="1", summary="機場提貨"),
     *         @OA\Examples(example="旅店提貨", value="2", summary="旅店提貨"),
     *     ),
     *     @OA\Parameter(
     *         name="take_time",
     *         description="預計提貨日期",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date"),
     *         example="2021-10-15",
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
     *     @OA\Parameter(
     *         name="promotion_code",
     *         description="促銷代碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="points",
     *         description="購物金",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="使用購物金", value="1", summary="使用購物金"),
     *     ),
     *     @OA\Parameter(
     *         name="shoppingCartIds[]",
     *         description="購物車IDs (未選擇時取全部購物車計算)",
     *         required=false,
     *         in="query",
     *      @OA\Schema(
     *        type="array",
     *        @OA\Items(type="integer"),
     *      )
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得可結帳購物車金額及使用者相關資料。",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartAmountSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode = 999 參數不存在/參數錯誤。 appCode = 0 折扣碼錯誤或已過期",
     *     ),
     *     security={{"webAuth": {}}}
     * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/shoppingCart/checkPromoCode",
     *     operationId="webUserShoppingCartCheckPromoCode",
     *     tags={"購物車"},
     *     summary="檢查促銷代碼",
     *     description="檢查促銷代碼，促銷代碼若為 VISA from_country_id 與 to_country_id 必填",
     *     @OA\Parameter(
     *         name="promotion_code",
     *         description="促銷代碼",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="TWPAY",
     *     ),
     *     @OA\Parameter(
     *         name="from_country_id",
     *         description="發貨地區國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣發貨", value="1", summary="台灣發貨"),
     *         @OA\Examples(example="日本發貨", value="5", summary="日本發貨"),
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="提貨地點國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣", value="1", summary="台灣"),
     *         @OA\Examples(example="中國", value="2", summary="中國"),
     *         @OA\Examples(example="日本", value="5", summary="日本"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, appCode = 0, 促銷代碼可用, data = 1。",
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode = 999 參數不存在/參數錯誤， appCode = 9, 促銷代碼無效或過期",
     *     ),
     *     security={{"webAuth": {}}}
     * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/shoppingCart",
     *     operationId="webUserShoppingCartList",
     *     tags={"購物車"},
     *     summary="前台-使用者購物車列表",
     *     description="前台-使用者購物車列表
1. 未登入時，已客戶端 session 作為購物車驗證，需使用 uuid 編碼字串。
2. 發貨地區 from_country_id 目前只有台灣 (1) 與日本 (5)。
3. 提貨地點 to_country_id 非台灣 (1) 地區則 shipping_method_id 只有 指定地點 (4)。
4. 提貨地點為台灣地區時， shipping_method_id 可有機場提貨 (1) 、旅店提貨 (2) 、 指定地點 (4) ， 預計提貨日期 take_time 為必填。
5. 語言代號 lang (en,jp,kr,th) 未填寫以中文為主，語言代號作變更，無翻譯資料則以英文代替，若無英文資料則改為中文替代。
6. 促銷代碼 promotion_code。
7. 購物金 points， 1: 全部使用 null:不使用，無登入則不帶入。
8. 商品總金額相關資料會根據填入相關資料作計算，並提供可否結帳的產品資料。",
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Parameter(
     *         name="from_country_id",
     *         description="發貨地區國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣發貨", value="1", summary="台灣發貨"),
     *         @OA\Examples(example="日本發貨", value="5", summary="日本發貨"),
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="提貨地點國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣", value="1", summary="台灣"),
     *         @OA\Examples(example="中國", value="2", summary="中國"),
     *         @OA\Examples(example="日本", value="5", summary="日本"),
     *     ),
     *     @OA\Parameter(
     *         name="shipping_method_id",
     *         description="物流方式id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="寄送海外/指定地點", value="4", summary="寄送海外/指定地點"),
     *         @OA\Examples(example="機場提貨", value="1", summary="機場提貨"),
     *         @OA\Examples(example="旅店提貨", value="2", summary="旅店提貨"),
     *     ),
     *     @OA\Parameter(
     *         name="take_time",
     *         description="預計提貨日期",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date"),
     *         example="2021-06-15",
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
     *     @OA\Parameter(
     *         name="promotion_code",
     *         description="促銷代碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="points",
     *         description="購物金",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="不使用購物金", value="0", summary="不使用購物金"),
     *         @OA\Examples(example="使用購物金", value="1", summary="使用購物金"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者購物車資料及相關計算資料。",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, 參數不存在/參數錯誤。",
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Post(
     *     path="/web/v1/shoppingCart",
     *     operationId="webUserShoppingCartCreate",
     *     tags={"購物車"},
     *     summary="前台-使用者新增購物車資料",
     *     description="前台-使用者新增購物車資料
1. 新增或修改購物車資料時，若是票券商品時將會清除清除購物車中的非票券商品，反之將會清除購物車中的票券商品。",
     *     @OA\Parameter(
     *         name="product_model_id",
     *         description="產品 model_id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="22125",
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         description="數量",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="2",
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, appCode = 0, 新增成功。",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartCreateSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode = 999 參數不存在/參數錯誤。",
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/shoppingCart/{id}",
     *     operationId="webUserShoppingCartShow",
     *     tags={"購物車"},
     *     summary="前台-使用者購物車資料",
     *     description="前台-使用者購物車資料",
     *     @OA\Parameter(
     *         name="id",
     *         description="購物車id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
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
     *         description="Success, appCode = 0, 購物車資料",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartShowSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode = 999 參數不存在/參數錯誤。",
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Patch(
     *     path="/web/v1/shoppingCart/{id}",
     *     operationId="webUserShoppingCartUpdate",
     *     tags={"購物車"},
     *     summary="前台-使用者購物車修改",
     *     description="前台-使用者購物車修改。",
     *     @OA\Parameter(
     *         name="id",
     *         description="購物車id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         example="53389",
     *     ),
     *     @OA\Parameter(
     *         name="product_model_id",
     *         description="產品 model_id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="22125",
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         description="數量",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="2",
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Response(response=200,description="Success, app code = 0 修改成功",
     *          @OA\JsonContent(ref="#/components/schemas/shoppingCartUpdateSuccess")
     *     ),
     *     @OA\Response(response=400,description="Error, appCode = 999 參數驗證失敗。"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Delete(
     *     path="/web/v1/shoppingCart/{id}",
     *     operationId="webUserShoppingCartDelete",
     *     tags={"購物車"},
     *     summary="前台-使用者購物車刪除",
     *     description="前台-使用者購物車刪除。",
     *     @OA\Parameter(
     *         name="id",
     *         description="購物車id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="網域",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Response(response=200,description="Success, app code = 0 刪除成功",
     *     ),
     *     @OA\Response(response=400,description="Error, appCode = 999 參數驗證失敗。"),
     *     security={{"webAuth": {}}}
    * )
    */
