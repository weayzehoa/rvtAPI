<?php
    /**
     * @OA\Get(
     *     path="/web/v1/vendor/{id}",
     *     operationId="webVendorItem",
     *     tags={"商家"},
     *     summary="前台-取得商家項目",
     *     description="透過 商家id 取得商家項目，有輸入 authentication token 時，可取得 is_favorite 欄位 1:使用者最愛商品 0:非使用者最愛商品",
     *     @OA\Parameter(
     *         name="id",
     *         description="The id of the Vendor",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         example="20",
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
     *         description="商家資料, 只有商家啟用才會顯示",
     *         @OA\JsonContent(ref="#/components/schemas/Vendor")
     *     ),
     *     @OA\Response(response=404,description="資料不存在。"),
     *     security={{"webAuth": {}}}
     * )
     */
