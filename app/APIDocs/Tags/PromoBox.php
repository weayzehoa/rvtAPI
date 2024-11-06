<?php
    /**
     * @OA\Get(
     *     path="/web/v1/promoBox",
     *     operationId="webPromoBoxList",
     *     tags={"優惠活動資料"},
     *     summary="前台-優惠活動資料列表",
     *     description="輸入 lang 參數可取得相對應語言資料，相對應語言找不到時，以英文優先，若也無英文資料則改為中文。",
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
     *         @OA\JsonContent(ref="#/components/schemas/PromoBoxSuccess")
     *     ),
     *     @OA\Response(response=400,description="Failure, 參數不存在/參數錯誤"),
    * )
    */
