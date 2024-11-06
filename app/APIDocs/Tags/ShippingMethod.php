<?php
    /**
     * @OA\Get(
     *     path="/web/v1/shippingMethod",
     *     operationId="shippingMethodList",
     *     tags={"物流與寄送方式"},
     *     summary="通用-寄送方式資料列表",
     *     description="通用-寄送方式資料列表",
     *     @OA\Response(response=200,description="Success, 取得寄送方式資料列表",
     *         @OA\JsonContent(ref="#/components/schemas/shippingMethodSuccess")
     *     ),
    * )
    */

    /**
     * @OA\Get(
     *     path="/web/v1/logisticList",
     *     operationId="webLogisticList",
     *     tags={"物流與寄送方式"},
     *     summary="前台-物流資料列表",
     *     description="前台-物流資料列表",
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
     *     @OA\Response(response=200,description="Success, 取得物流資料列表",
     *          @OA\JsonContent(ref="#/components/schemas/LogisticSuccess")
     *     ),
    * )
    */
