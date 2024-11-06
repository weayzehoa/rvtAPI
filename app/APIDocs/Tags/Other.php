<?php
    /**
     * @OA\Get(
     *     path="/uuid",
     *     operationId="uuidGenerate",
     *     tags={"其它"},
     *     summary="UUID 產生器",
     *     description="UUID 產生器",
     *     @OA\Response(response=200,description="Success, 取得 UUID，ex: 72c1a308-154f-4867-94ad-963bda03f93d",
     *     ),
    * )
    */
    /**
     * @OA\Get(
     *     path="/language",
     *     operationId="language",
     *     tags={"其它"},
     *     summary="語言包",
     *     description="語言包資料，預設中文",
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號 (en,jp,kr,th)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(response=200,description="Success, 取得該語言包資料, [{key_value : 語言資料,key_value : 語言資料,...,key_value : 語言資料}]"),
    * )
    */
