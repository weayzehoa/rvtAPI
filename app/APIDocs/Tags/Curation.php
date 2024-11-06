<?php
    /**
     * @OA\Get(
     *     path="/web/v1/curation",
     *     operationId="webCurationList",
     *     tags={"策展"},
     *     summary="前台-首頁及分類策展列表",
     *     description="前台-取得首頁及分類策展列表，請使用 cate 參數， home 代表首頁， category 代表分類，有輸入 authentication token 時，產品資料中可取得 is_favorite 欄位 1:使用者最愛商品 0:非使用者最愛商品",
     *     @OA\Parameter(
     *         name="cate",
     *         description="策展類別 (home or category)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="策展類別 - home", value="home", summary="策展類別 - home"),
     *         @OA\Examples(example="策展類別 - product", value="category", summary="策展類別 - category"),
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
     *         description="Success, 取得首頁、分類或商家策展列表資料",
     *          @OA\JsonContent(ref="#/components/schemas/CurationSuccess")
     *     ),
     *     @OA\Response(response=400,description="Failure, 參數不存在/參數錯誤"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/curation/{id}",
     *     operationId="webCurationItem",
     *     tags={"策展"},
     *     summary="前台-首頁或分類策展項目",
     *     description="透過 id 取得首頁或分類策展項目，有輸入 authentication token 時，產品資料中可取得 is_favorite 欄位 1:使用者最愛商品 0:非使用者最愛商品",
     *     @OA\Parameter(
     *         name="id",
     *         description="The id of the Curation",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer"),
     *         example="1",
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
     *     @OA\Response(response=200,description="策展項目資料, 只有啟用及時間範圍內才會顯示",
     *         @OA\JsonContent(ref="#/components/schemas/curation")
     *     ),
     *     @OA\Response(response=404,description="資料不存在。"),
     *     security={{"webAuth": {}}}
     * )
     */
