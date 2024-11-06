<?php
    /**
     * @OA\Get(
     *     path="/web/v1/airportLocation",
     *     operationId="webAirportList",
     *     tags={"機場地址"},
     *     summary="前台-機場地址資料列表",
     *     description="1. 輸入 to_country_id 取得該國家機場地址資料。
2. 輸入 lang 參數只能取英文資料，無英文資料則以中文替代。
3. name 欄位為顯示，value 欄位為帶入訂單中的資料。",
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="台灣", value="1", summary="台灣"),
     *         @OA\Examples(example="日本", value="5", summary="日本"),
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
     *         description="Success, 取得該國家機場地址資料",
     *         @OA\JsonContent(ref="#/components/schemas/AirportLocationSuccess")
     *     ),
     *     @OA\Response(response=400,
     *         description="Error, appCode 999, 參數錯誤/參數不存在",
     *     ),
    * )
    */
