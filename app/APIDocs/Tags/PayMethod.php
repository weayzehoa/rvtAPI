<?php
    /**
     * @OA\Get(
     *     path="/web/v1/payMethod",
     *     operationId="webPayMethodList",
     *     tags={"付款方式"},
     *     summary="前台-付款方式資料列表",
     *     description="1. 未輸入任何參數只會取得已啟用的付款方式資料。
2. 輸入 lang 參數只能取英文資料。
3. name 欄位為顯示，value 欄位為帶入訂單中的資料。",
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
     *         description="Success, 取得付款方式資料列表",
     *         @OA\JsonContent(ref="#/components/schemas/PayMethodListSuccess")
     *     ),
    * )
    */
