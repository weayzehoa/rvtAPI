<?php
    /**
     * @OA\GET(
     *     path="/web/v1/userAddress",
     *     operationId="webUserAddressList",
     *     tags={"使用者常用地址"},
     *     summary="前台-使用者常用地址列表",
     *     description="前台-使用者常用地址列表，
1. 此 API 等同 /web/v1/user/{id}?type=address
2. 輸入 to_country_id 可取得特定國家常用地址資料，用在購物車結帳時。",
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="國家id",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者常用地址列表資料",
     *         @OA\JsonContent(ref="#/components/schemas/userAddressList")
     *     ),
     *     @OA\Response(response=400,description="Failure, 參數不存在/參數錯誤"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\GET(
     *     path="/web/v1/userAddress/{id}",
     *     operationId="webUserAddressShow",
     *     tags={"使用者常用地址"},
     *     summary="前台-使用者常用地址資料",
     *     description="前台-使用者常用地址資料",
     *     @OA\Parameter(
     *         name="id",
     *         description="常用地址id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者常用地址資料",
     *         @OA\JsonContent(ref="#/components/schemas/userAddressShow")
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/userAddress",
     *     operationId="webUserAddressCreate",
     *     tags={"使用者常用地址"},
     *     summary="前台-使用者常用地址新增",
     *     description="前台-使用者常用地址新增。
1. 國家為 美國、韓國， 名字與地址 僅限使用英文。
2. 國家為 台灣、中國、香港， city 、 area 必填
3. 國家為 中國， province 必填。",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     description="名字 (string max:40)",
     *                     type="string",
     *                     example="直流電通",
     *                 ),
     *                 @OA\Property(
     *                     property="nation",
     *                     description="國際碼 (integer max:10)",
     *                     type="integer",
     *                     example="86",
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     description="電話號碼 (僅允許數字, string max:20)",
     *                     type="integer",
     *                     example="906486688",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="電子郵件 (email 驗證, string max:255)",
     *                     type="string",
     *                     example="icarry@icarry.me",
     *                 ),
     *                 @OA\Property(
     *                     property="country",
     *                     description="國家 (string max:20)",
     *                     type="string",
     *                     example="中國",
     *                 ),
     *                 @OA\Property(
     *                     property="city",
     *                     description="省/市 (string max:20)",
     *                     type="string",
     *                     example="黑龙江省",
     *                 ),
     *                 @OA\Property(
     *                     property="area",
     *                     description="市/區 (string max:20)",
     *                     type="string",
     *                     example="哈尔滨市",
     *                 ),
     *                 @OA\Property(
     *                     property="s_area",
     *                     description="區 (string max:20)",
     *                     type="string",
     *                     example="南岗区",
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     description="地址 (string max:255)",
     *                     type="string",
     *                     example="南京東路三段103號11樓之1",
     *                 ),
     *                 @OA\Property(
     *                     property="zip_code",
     *                     description="郵政碼 (integer max:10)",
     *                     type="integer",
     *                     example="104",
     *                 ),
     *                 @OA\Property(
     *                     property="is_default",
     *                     description="設定預設 (integer 1:是 0:否)",
     *                     type="integer",
     *                     example="0",
     *                 ),
     *                 @OA\Property(
     *                     property="china_id_img1",
     *                     description="中國身分證正面",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="china_id_img2",
     *                     description="中國身分證反面",
     *                     type="file",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200,description="Success, app code = 0 新增成功",),
     *     @OA\Response(response=400,description="Error, app code = 999 參數驗證失敗、app code = 9 發送過於頻繁，請稍後再試。"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\PATCH(
     *     path="/web/v1/userAddress/{id}",
     *     operationId="webUserAddressUpdate",
     *     tags={"使用者常用地址"},
     *     summary="前台-使用者常用地址修改 (這邊測試壞掉了, 請改用postman或其他的測試程式)",
     *     description="前台-使用者常用地址修改。
1. 國家為 美國、韓國， 名字與地址 僅限使用英文。
2. 國家為 台灣、中國、香港， city 、 area 必填。
3. 國家為 中國， province 必填。",
     *     @OA\Parameter(
     *         name="id",
     *         description="常用地址id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     description="名字 (string max:40)",
     *                     type="string",
     *                     example="直流電通",
     *                 ),
     *                 @OA\Property(
     *                     property="nation",
     *                     description="國際碼 (integer max:10)",
     *                     type="integer",
     *                     example="86",
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     description="電話號碼 (僅允許數字, string max:20)",
     *                     type="integer",
     *                     example="906486688",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="電子郵件 (email 驗證, string max:255)",
     *                     type="string",
     *                     example="icarry@icarry.me",
     *                 ),
     *                 @OA\Property(
     *                     property="country",
     *                     description="國家 (string max:20)",
     *                     type="string",
     *                     example="中國",
     *                 ),
     *                 @OA\Property(
     *                     property="city",
     *                     description="省/市 (string max:20)",
     *                     type="string",
     *                     example="黑龙江省",
     *                 ),
     *                 @OA\Property(
     *                     property="area",
     *                     description="市/區 (string max:20)",
     *                     type="string",
     *                     example="哈尔滨市",
     *                 ),
     *                 @OA\Property(
     *                     property="s_area",
     *                     description="區 (string max:20)",
     *                     type="string",
     *                     example="南岗区",
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     description="地址 (string max:255)",
     *                     type="string",
     *                     example="南京東路三段103號11樓之1",
     *                 ),
     *                 @OA\Property(
     *                     property="zip_code",
     *                     description="郵政碼 (integer max:10)",
     *                     type="integer",
     *                     example="104",
     *                 ),
     *                 @OA\Property(
     *                     property="is_default",
     *                     description="設定預設 (integer 1:是 0:否)",
     *                     type="integer",
     *                     example="0",
     *                 ),
     *                 @OA\Property(
     *                     property="china_id_img1",
     *                     description="中國身分證正面",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="china_id_img2",
     *                     description="中國身分證反面",
     *                     type="file",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200,description="Success, app code = 0 更新成功",),
     *     @OA\Response(response=400,description="Error, app code = 999 參數驗證失敗、app code = 9 發送過於頻繁，請稍後再試。"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\DELETE(
     *     path="/web/v1/userAddress/{id}",
     *     operationId="webUserAddressDel",
     *     tags={"使用者常用地址"},
     *     summary="前台-使用者常用地址刪除",
     *     description="前台-使用者常用地址刪除。",
     *     @OA\Parameter(
     *         name="id",
     *         description="常用地址id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success, 刪除成功。"),
     *     @OA\Response(response=404, description="Error, 資料不存在。"),
     *     security={{"webAuth": {}}}
    * )
    */
