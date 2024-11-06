<?php
    /**
     * @OA\POST(
     *     path="/web/v1/userFavorite",
     *     operationId="webUserFavoriteAdd",
     *     tags={"使用者心願清單"},
     *     summary="前台-使用者新增心願清單 (type = product、vendor 共用)",
     *     description="前台-使用者新增心願清單。
1. type 欄位輸入 product 或 vendor 參數，切換成商品或商家。
2. id 欄位輸入 商品 id 或 商家 id 。",
     *     @OA\Parameter(
     *         name="id",
     *         description="Product or Vendor id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         description="類型 - 產品 or 商家",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="類型 - product", value="product", summary="類型 - 產品"),
     *         @OA\Examples(example="類型 - vendor", value="points", summary="類型 - 商家"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 新增成功。 Error, 已存在。",
     *     ),
     *     @OA\Response(response=400, description="Failure, 參數不存在/參數錯誤"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\DELETE(
     *     path="/web/v1/userFavorite/{id}",
     *     operationId="webUserFavoriteDel",
     *     tags={"使用者心願清單"},
     *     summary="前台-使用者移除心願清單 (type = product、vendor 共用)",
     *     description="前台-使用者移除心願清單。
1. type 欄位輸入 product 或 vendor 參數，切換成商品或商家。
2. id 欄位輸入 商品 id 或 商家 id 。",
     *     @OA\Parameter(
     *         name="id",
     *         description="Product or Vendor id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         description="類型 - 產品 or 商家",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="類型 - product", value="product", summary="類型 - 產品"),
     *         @OA\Examples(example="類型 - vendor", value="points", summary="類型 - 商家"),
     *     ),
     *     @OA\Response(response=200, description="Success, 移除成功。"),
     *     @OA\Response(response=400, description="Failure, 參數不存在/參數錯誤"),
     *     @OA\Response(response=404, description="Error, 資料不存在。"),
     *     security={{"webAuth": {}}}
    * )
    */
