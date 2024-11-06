<?php
    /**
     * @OA\POST(
     *     path="/web/v1/sendVerifyCode",
     *     operationId="webSendVerifyCode",
     *     tags={"使用者登入登出"},
     *     summary="前台-發送驗證碼",
     *     description="前台-發送驗證碼，
1. 輸入 type = register ，用於使用者註冊時。
2. 輸入 type = resetPassword ，用於使用者忘記密碼時。",
     *     @OA\Parameter(
     *         name="type",
     *         description="類型 (register or resetPassword)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="註冊 - register", value="register", summary="註冊 - register"),
     *         @OA\Examples(example="重設密碼 - resetPassword", value="resetPassword", summary="重設密碼 - resetPassword"),
     *     ),
     *     @OA\Parameter(
     *         name="nation",
     *         description="國際碼(帶+號)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         description="手機號碼",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from_site",
     *         description="哪邊來的?",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, appCode = 0: 驗證碼已傳送。",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error, appCode = 999, 參數不存在/參數錯誤"
     *     ),
     *     @OA\Response(response=403,
     *         description="Error, 使用者狀態錯誤拒絕發送驗證碼，appCode = -1: 已此手機號碼已停用。、appCode = 0: 此手機號碼尚未完成註冊程序。(type=resetPassword)、appCode = 1: 此手機號碼已註冊。(type=register)、appCode = 2: 此手機號碼不存在/驗證碼錯誤。(type=resetPassword)、appCode = 9: 發送過於頻繁 (延遲1分鐘)"
     *     ),
     *     @OA\Response(response=500,description="Error, 發送失敗，appCode = 99: 簡訊商系統錯誤"),
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/confirmVerifyCode",
     *     operationId="webConfirmVerifyCode",
     *     tags={"使用者登入登出"},
     *     summary="前台-驗證碼檢驗",
     *     description="前台-驗證碼檢驗。",
     *     @OA\Parameter(
     *         name="verifyCode",
     *         description="驗證碼 (4個數字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
    *     @OA\Parameter(
     *         name="nation",
     *         description="國際碼(帶+號)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         description="手機號碼",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from_site",
     *         description="哪邊來的?",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, appCode = 0: 驗證碼驗證成功。",
     *     ),
     *     @OA\Response(response=400, description="Error, appCode = 9: 驗證碼驗證失敗。"),
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/register",
     *     operationId="webRegister",
     *     tags={"使用者登入登出"},
     *     summary="前台-註冊資料",
     *     description="前台-註冊資料。",
     *     @OA\Parameter(
     *         name="verifyCode",
     *         description="驗證碼 (4個數字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
    *     @OA\Parameter(
     *         name="nation",
     *         description="國際碼(帶+號)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         description="手機號碼",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         description="名字",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         description="Email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         description="密碼(最少4個字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirm",
     *         description="確認密碼(最少4個字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refer_code",
     *         description="推薦碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="from_site",
     *         description="從哪邊來",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="roger",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, appCode = 0: 註冊完成。自動登入，取得 Authorization 授權。",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error, appCode = 999: 參數不存在/參數錯誤、 appCode = 3: 推薦碼錯誤/不存在/已過期。",
     *     ),
     *     @OA\Response(response=403,
     *         description="Error, 使用者狀態錯誤拒絕發送驗證碼，appCode = -1: 已此手機號碼已停用。、appCode = 1: 此手機號碼已註冊。、appCode = 2: 此手機號碼不存在/驗證碼錯誤。"
     *     ),
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/forgetPassword",
     *     operationId="webForgetPassword",
     *     tags={"使用者登入登出"},
     *     summary="前台-忘記密碼修改",
     *     description="前台-忘記密碼修改資料。",
     *     @OA\Parameter(
     *         name="verifyCode",
     *         description="驗證碼 (4個數字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
    *     @OA\Parameter(
     *         name="nation",
     *         description="國際碼(帶+號)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         description="手機號碼",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         description="密碼(最少4個字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirm",
     *         description="確認密碼(最少4個字)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="from_site",
     *         description="哪邊來的?",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, appCode = 0: 密碼更新完成。自動登入，取得 Authorization 授權。",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error, appCode = 999: 參數不存在/參數錯誤、 appCode = 3: 推薦碼錯誤/不存在/已過期。",
     *     ),
     *     @OA\Response(response=403,
     *         description="Error, 使用者狀態錯誤拒絕發送驗證碼，appCode = -1: 已此手機號碼已停用。、appCode = 0: 此手機號碼尚未完成註冊。、appCode = 2: 此手機號碼不存在/驗證碼錯誤。"
     *     ),
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/login",
     *     operationId="webUserLogin",
     *     tags={"使用者登入登出"},
     *     summary="前台-使用者登入",
     *     description="前台-使用者登入，取得使用者 id 及授權 token 資料。
1. 第一次登入後，可取得使用者 remember_me 資料，將其放置於 cookie 中，之後可透過使用 remeber_me 來讓使用者保持永久登入。
2. 透過 remeber_me ，可免填 nation 、 moblie 、 password，直接登入取得授權 token。",
     *     @OA\Parameter(
     *         name="nation",
     *         description="國際碼(帶+號)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         description="手機號碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         description="密碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="from_site",
     *         description="哪邊來的?",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="remember_me",
     *         description="Remeber Me Token",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者 id 及 Token 授權",
     *          @OA\JsonContent(ref="#/components/schemas/userLoginSuccess")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error, appCode = 999, 參數不存在/參數錯誤"
     *     ),
     *     @OA\Response(response=401,
     *         description="Error, appCode = 0, 使用者尚未通過驗證。"
     *     ),
     *     @OA\Response(response=403,
     *         description="Error, appCode = -1, 使用者已停用。"
     *     ),
     *     @OA\Response(response=404,
     *         description="Error, appCode = 9, 使用者不存在或帳號密碼錯誤。"
     *     ),
     * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/logout",
     *     operationId="webUserLogout",
     *     tags={"使用者登入登出"},
     *     summary="前台-使用者登出",
     *     description="前台-使用者登出，清除使用者授權 token 資料",
     *     @OA\Response(response=200,
     *         description="Success, 清除使用者授權 token 資料",
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\POST(
     *     path="/web/v1/refresh",
     *     operationId="webUserTokenRefresh",
     *     tags={"使用者登入登出"},
     *     summary="前台-手動更新token",
     *     description="前台-使用者手動更新token資料",
     *     @OA\Response(response=200,
     *         description="Success, 獲得新的授權 token 資料",
     *         @OA\JsonContent(ref="#/components/schemas/userLoginSuccess")
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
