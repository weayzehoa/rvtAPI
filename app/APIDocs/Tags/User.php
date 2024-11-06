<?php
    /**
     * @OA\GET(
     *     path="/web/v1/user/{id}",
     *     operationId="webUser",
     *     tags={"使用者個人資料"},
     *     summary="前台-使用者個人資料 (type = profile、points、address、favorites、orders 共用)",
     *     description="前台-使用者個人資料，取得使用者id及授權token資料後，透過id取得該使用者相關資料。
1. 輸入 type (profile、points、address、favorites、orders) 參數，可取得相對應的資料。
2. 輸入 lang 語言參數取得產品及商家英文資料，此參數只有在 type = favorites 有效。
3. 未輸入任何參數，預設僅取得 使用者 id 、 name 、 nation 及 mobile 資料欄位。",
     *     @OA\Parameter(
     *         name="id",
     *         description="使用者id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         description="使用者資料類型",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="類型 - 不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="類型 - profile", value="profile", summary="類型 - profile"),
     *         @OA\Examples(example="類型 - points", value="points", summary="類型 - points"),
     *         @OA\Examples(example="類型 - address", value="address", summary="類型 - address"),
     *         @OA\Examples(example="類型 - favorites", value="favorites", summary="類型 - favorites"),
     *         @OA\Examples(example="類型 - orders", value="orders", summary="類型 - orders"),
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
     *         description="Success, 取得使用者個人資料。",
     *          @OA\JsonContent(ref="#/components/schemas/userSuccess")
     *     ),
     *     @OA\Response(response=400,description="Failure, 參數不存在/參數錯誤"),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\PATCH(
     *     path="/web/v1/user/{id}",
     *     operationId="webUserUpdate",
     *     tags={"使用者個人資料"},
     *     summary="前台-使用者個人資料更新 (type = editProfile、changePassword 共用)",
     *     description="前台-使用者個人資料更新，與 編輯個人資料 (type = editProfile) 及 變更密碼 (type = changePassword) 共用。
1. type = editProfile，name 與 email 必填，refer_id 與 asiamiles_account 選填。
2. type = changePassword， currentPassword 、 password 及 passwordConfirm 必填， password 與 passwordConfirm 必須相同， currentPassword 與 password 不可相同。",
     *     @OA\Parameter(
     *         name="id",
     *         description="使用者id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="type",
     *                     description="類型，editProfile、changePassword",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     description="名字 (string 40)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="電子郵件 (string max:255)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="refer_id",
     *                     description="推薦碼 (string max:11)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="asiamiles_account",
     *                     description="亞洲萬里通帳號 (digits 10)",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="currentPassword",
     *                     description="現用密碼 (string min:4)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="密碼 (string min:4)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="passwordConfirm",
     *                     description="密碼 (string min:4)",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200,description="Success, appCode = 0 更新成功, 返回附加 Token 至 Header",),
     *     @OA\Response(response=400,description="Error, appCode = 999 參數驗證失敗、app code = 0 使用者資料不存在、app code = 1 推薦碼無法填寫自己代碼、app code = 2 推薦碼只能使用一次、app code = 3 推薦碼不存在或已失效。"),
     *     @OA\Response(response=403,description="Error, type 參數錯誤。"),
     *     security={{"webAuth": {}}}
    * )
    */
