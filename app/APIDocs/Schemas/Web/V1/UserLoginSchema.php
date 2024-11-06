<?php
/**
 * @OA\Schema(
 *     schema="userLoginSuccess",
 *     type="object",
 *     title="前台-使用者登入欄位說明"
 * )
 */
class userLoginSuccess
{
    /**
     * 狀態
     * @var string
     * @OA\Property(format="string", example="Success")
     */
    public $status;
    /**
     * 使用者id
     * @var integer
     * @OA\Property(format="integer", example="10023")
     */
    public $user_id;
    /**
     * Remember Me token
     * @var string
     * @OA\Property(format="string", example="xvcCYpBUISCrtMl5T8wPVGBrVXNnsRLjrhD....")
     */
    public $remember_me;
    /**
     * 授權 token
     * @var string
     * @OA\Property(format="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1....")
     */
    public $access_token;
    /**
     * 授權 token 類型
     * @var string
     * @OA\Property(format="string", example="bearer")
     */
    public $token_type;
    /**
     * 授權 token 過期時間 (分鐘)
     * @var integer
     * @OA\Property(format="integer", example="30")
     */
    public $expires_in;
}
