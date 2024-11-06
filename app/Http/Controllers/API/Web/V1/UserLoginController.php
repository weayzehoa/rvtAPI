<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\GateSmsLog as SmsLogDB;
use App\Models\iCarryReferCode as ReferCodeDB;
use App\Jobs\AdminSendSMS;
use Carbon\Carbon;
use Str;
use DB;

class UserLoginController extends Controller
{
    //登入驗證規則
    protected $loginRules = [
        // 'nation' => 'required|regex:/^[0-9]+$/',
        // 'nation' => 'required_if:remeber_token,null|numeric',
        'nation' => 'required_if:remember_me,null|required_with:mobile|string|regex:/^[+o0-9]+$/|max:5',
        'mobile' => 'required_if:remember_me,null|numeric',
        'password' => 'required_if:remember_me,null|required_with:mobile|min:4',
        'remember_me' => 'required_without:mobile|string|max:100',
        'from_site' => 'required|string|max:10',
    ];
    //發送驗證碼驗證規則
    protected $sendVerifyCodeRules = [
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:5',
        'mobile' => 'required|numeric',
        'type' => 'required|in:register,resetPassword',
        'from_site' => 'required|string|max:10',
    ];
    //檢驗驗證碼驗證規則
    protected $confirmVerifyCodeRules = [
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:5',
        'mobile' => 'required|numeric',
        'verifyCode' => 'required|numeric',
        'from_site' => 'required|string|max:10',
    ];

    //註冊資料
    protected $registerRules = [
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:5',
        'mobile' => 'required|numeric',
        'verifyCode' => 'required|numeric',
        'name' => 'required|string|max:40',
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:4',
        'password_confirm' => 'required|same:password|min:4',
        'refer_code' => 'nullable|string|max:10',
        'from_site' => 'required|string|max:20',
        'from_token' => 'nullable|string|max:128',
    ];

    //忘記密碼
    protected $forgetPasswordRules = [
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:5',
        'mobile' => 'required|numeric',
        'verifyCode' => 'required|numeric',
        'password' => 'required|string|min:4',
        'password_confirm' => 'required|same:password|min:4',
        'from_site' => 'required|string|max:10',
    ];

    public function __construct()
    {
        //除了 login 其餘 function 都要經過 api 的 middleware 檢查
        $this->middleware(['api','refresh.token'], ['except' => ['login','confirmVerifyCode','sendVerifyCode','register','forgetPassword']]);
        $this->aesKey = env('APP_AESENCRYPT_KEY');
    }
    //登入
    public function login(Request $request)
    {
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $this->loginRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->loginRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($this->loginRules))){
                $$key = $value;
            }
        }
        //檢查使用者是否存在
        !empty($mobile) ? $mobile = ltrim($mobile,'0') : '';
        !empty($nation) && $nation != 'o' ? $nation = '+'.str_replace('+','',$nation) : '';
        $user = UserDB::where('from_site',$from_site);
        if(!empty($remember_me)){
            $user = $user->where('remember_me',$remember_me);
        }else{
            $user = $user->whereRaw(" AES_DECRYPT(mobile,'$this->aesKey') = '$mobile' ")
            ->where('password',sha1($password))
            ->where(function ($q) use ($nation) {
                $q->where('nation', $nation)->orWhere('nation', '');
            });
        }
        $user = $user->select([
            'id',
            'status',
            'remember_me',
        ])->first();
        if (!empty($user)) {
            if($user->status == -1){
                return $this->appCodeResponse('Error', -1, '使用者已停用', 403);
            }elseif($user->status == 0){
                return $this->appCodeResponse('Error', 0, '使用者尚未通過驗證', 401);
            }elseif($user->status == 1){
                //更新remember_me欄位
                if(empty($user->remember_me)){
                    $user->fill(['remember_me' => Str::random(100)])->save();
                }
                //紀錄
                $token = auth('webapi')->login($user);
                return $this->respondWithToken($user,$token);
                //檢查是否已經登入過, 有則註銷舊的Token, 這樣是否會造成另一個瀏覽器登入被登出?
                // return $this->debugResponse(auth('webapi')->getPayload());
                // if(auth('webapi')->check()){
                //     auth('webapi')->invalidate(true);
                // }
            }
        }
        return $this->appCodeResponse('Error', 9, '使用者不存在或帳號密碼錯誤', 404);
    }
    //登出
    public function logout(Request $request)
    {
        auth('webapi')->logout();
        return $this->appCodeResponse('Success', 0, '登出成功', 200);
    }
    //手動更新token
    public function refresh()
    {
        $id = auth('webapi')->user()->id;
        $user = UserDB::findOrFail($id);
        $newToken = auth('webapi')->refresh(true, true);
        return $this->respondWithToken($user,$newToken);
    }

    //開發測試用
    public function me()
    {
        return response()->json(auth('webapi')->user());
    }

    //拋出token
    protected function respondWithToken($user,$token)
    {
        return response()->json([
            'status' => 'Success',
            'user_id' => $user->id,
            'remember_me' => $user->remember_me,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('webapi')->factory()->getTTL(),
        ],200)->header('Authorization','Bearer '.$token);
    }

    //發送驗證碼
    public function sendVerifyCode(Request $request)
    {
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $this->sendVerifyCodeRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->sendVerifyCodeRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($this->sendVerifyCodeRules))){
                $$key = $value;
            }
        }
        //檢查使用者是否存在
        $mobile = ltrim($mobile,'0');
        $nation != 'o' ? $nation = '+'.str_replace(['+','-','_'],['','',''],$nation) : '';
        $fullMobile = $nation.$mobile;
        $user = UserDB::whereRaw(" AES_DECRYPT(mobile,'$this->aesKey') = '$mobile' ")
        ->where('from_site',$from_site)
        ->where(function($q)use($nation){
            $q->where('nation',$nation)->orWhere('nation','');
        })->select(['id','status','verify_code'])->first();
        if(!empty($user)){
            if ($user->status == -1) {
                return $this->appCodeResponse('Error', -1, '此手機號碼已停用。', 403);
            }elseif ($user->status == 1) {
                if($type == 'resetPassword'){
                    return $this->sendCode($fullMobile, $user);
                }
                if ($type == 'register') {
                    return $this->appCodeResponse('Error', 1, '此手機號碼已註冊。', 403);
                }
            }elseif ($user->status == 0) {
                if ($type == 'resetPassword') {
                    return $this->appCodeResponse('Error', 0, '此手機號碼尚未完成註冊程序。', 403);
                }
                if ($type == 'register') { //未完成驗證一樣發送驗證碼
                    return $this->sendCode($fullMobile, $user);
                }
            }
        }else{
            if ($type == 'resetPassword') {
                return $this->appCodeResponse('Error', 2, '此手機號碼不存在/驗證碼錯誤。', 400);
            }
            if ($type == 'register') {
                return
                $user = UserDB::create([
                    'name' => 'new user',
                    'email' => "new user's email",
                    'nation' => $nation,
                    'mobile' => DB::raw("AES_ENCRYPT('$mobile', '$this->aesKey')"),
                    'status' => 0,
                    'from_site' => $from_site,
                ]);
                return $this->sendCode($fullMobile, $user);
            }
        }
    }

    //檢驗驗證碼
    public function confirmVerifyCode(Request $request)
    {
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $this->confirmVerifyCodeRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->confirmVerifyCodeRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($this->confirmVerifyCodeRules))){
                $$key = $value;
            }
        }
        //檢查使用者是否存在
         $mobile = ltrim($mobile,'0');
        $nation != 'o' ? $nation = '+'.str_replace(['+','-','_'],['','',''],$nation) : '';
        $fullMobile = $nation.$mobile;
        $user = UserDB::whereRaw(" AES_DECRYPT(mobile,'$this->aesKey') = '$mobile' ")
        ->where('from_site',$from_site)
        ->where('verify_code',$verifyCode)
        ->where(function($q)use($nation){
            $q->where('nation',$nation)->orWhere('nation','');
        })->select(['id','status','verify_code'])->first();
        if(!empty($user)){
            return $this->appCodeResponse('Success', 0, '驗證碼驗證成功。', 200);
        }else{
            return $this->appCodeResponse('Error', 9, '驗證碼驗證失敗。', 400);
        }
    }

    //註冊資料
    public function register(Request $request)
    {
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $this->registerRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->registerRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($this->registerRules))){
                $$key = $value;
                $data[$key] = $value;
            }
        }
        //檢查使用者是否存在
        $mobile = ltrim($mobile,'0');
        !empty($data['mobile']) ? $data['mobile'] = $mobile : '';
        $nation != 'o' ? $nation = '+'.str_replace('+','',$nation) : '';
        $nation != 'o' ? $data['nation'] = '+'.str_replace('+','',$nation) : '';
        $user = UserDB::whereRaw(" AES_DECRYPT(mobile,'$this->aesKey') = '$mobile' ")
        ->where('from_site',$from_site)
        ->where('verify_code',$verifyCode)
        ->where(function($q)use($nation){
            $q->where('nation',$nation)->orWhere('nation','');
        })->select(['id','status','verify_code'])->first();
        if(!empty($user)){
            if($user->status == -1){
                return $this->appCodeResponse('Error', -1, '此手機號碼已停用。', 403);
            }elseif($user->status == 1){
                return $this->appCodeResponse('Error', 1, '此手機號碼已註冊。', 403);
            }
        }else{
            return $this->appCodeResponse('Error', 2, '該手機號碼不存在/驗證碼錯誤', 400);
        }
        //密碼處理
        !empty($data['password']) ? $data['pwd'] = $data['password'] = sha1($data['password']) : '';
        //電話加密
        $data['mobile'] = DB::raw("AES_ENCRYPT('$mobile', '$this->aesKey')");
        //檢查註冊碼
        if(!empty($refer_code)){
            if(is_numeric($refer_code)){
                $chk = UserDB::where([['id',$refer_code],['status',1]])->first();
                if($chk){
                    $data['refer_id'] = $refer_code;
                    $data['refer_code'] = null;
                }
            }else{
                $chk = ReferCodeDB::where([['code',$refer_code],['status',1],['start_time','<=',date('Y-m-d H:i:s')],['end_time','>=',date('Y-m-d H:i:s')]])->first();
                if(!empty($chk)){
                    $data['refer_id'] = null;
                    $data['refer_code'] = $refer_code;
                }
            }
            if(empty($chk)){
                return $this->appCodeResponse('Error', 3, '推薦碼錯誤/不存在/已過期', 400);
            }
        }
        //購物金處理
        if(!empty($data['refer_id']) || !empty($data['refer_code'])){
            if(!empty($data['refer_code'])){
                $points = $chk->icarry_point;
                $chk->increment('total_register');
                $userPoint['point_type'] = "$chk->code 註冊推薦，贈送 $points 點";
            }else{
                $points = 100;
                $referId = $data['refer_id'];
                $userPoint['point_type'] = "獲得 $referId 推薦，贈送 $points 點";
            }
            $data['points'] = $user->points + $points;
            $userPoint['user_id'] = $user->id;
            $userPoint['points'] = $points;
            $userPoint['balance'] = $data['points'];
            $userPoint['dead_time'] = Carbon::now()->addDays(180);
            UserPointDB::create($userPoint);
        }
        //更新資料
        $data['status'] = 1;
        $data['remember_me'] = Str::random(100);
        $user->update($data);
        $token = auth('webapi')->login($user);
        return $this->appCodeResponse('Success', 1, '註冊完成。', 200)->header('Authorization','Bearer '.$token);
    }

    //忘記密碼
    public function forgetPassword(Request $request)
    {
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $this->forgetPasswordRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($request->all(), $this->forgetPasswordRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($this->forgetPasswordRules))){
                $$key = $value;
                $data[$key] = $value;
            }
        }
        //檢查使用者是否存在
        $data['mobile'] = $mobile = ltrim($mobile,'0');
        $nation != 'o' ? $nation = '+'.str_replace('+','',$nation) : '';
        $nation != 'o' ? $data['nation'] = '+'.str_replace('+','',$nation) : '';
        $user = UserDB::whereRaw(" AES_DECRYPT(mobile,'$this->aesKey') = '$mobile' ")
        ->where('from_site',$from_site)
        ->where('verify_code',$verifyCode)
        ->where(function($q)use($nation){
            $q->where('nation',$nation)->orWhere('nation','');
        })->select(['id','status','verify_code'])->first();
        if($user){
            if($user->status == -1){
                return $this->appCodeResponse('Error', -1, '此手機號碼已停用。', 403);
            }elseif($user->status == 0){
                return $this->appCodeResponse('Error', 0, '此手機號碼尚未完成註冊。', 403);
            }
            //密碼處理
            !empty($data['password']) ? $data['password'] = sha1($data['password']) : '';
            //電話加密
            $data['mobile'] = DB::raw("AES_ENCRYPT('$mobile', '$this->aesKey')");
            //更新密碼資料
            $token = auth('webapi')->login($user);
            $user = $user->update($data);
            return $this->appCodeResponse('Success', 1, '密碼更新完成。', 200)->header('Authorization','Bearer '.$token);
        }else{
            return $this->appCodeResponse('Error', 2, '此手機號碼不存在/驗證碼錯誤。', 400);
        }
    }

    private function sendCode($phone, $user)
    {
        $now = date('Y-m-d H:i:s');
        $smsLog = SmsLogDB::where('user_id',$user->id)->orderBy('created_at','desc')->first();
        //延遲1分鐘時間,避免連續發送
        if($smsLog){
            $now = strtotime(date('Y-m-d H:i:s'));
            $delay = strtotime($smsLog->created_at) + 60;
            if($now < $delay){
                return $this->appCodeResponse('Error', 9, '發送過於頻繁，請稍後再試。', 400);
            }
        }
        $sms = [];
        //產生隨機碼
        $verifyCode=rand(1000,9999);
        $user->update(['verify_code' => $verifyCode]);
        $message="$verifyCode (iCarry Verification Code)";
        $sms['user_id'] = $user->id;
        $sms['phone'] = $phone;
        $sms['message'] = substr($phone,0,3)=="+86" ? $verifyCode : (substr($phone,0,4)=="+886" ? $message="親愛的iCarry用戶您好，您的手機認證碼為".$verifyCode."，謝謝。" : $message);
        $sms['return'] = true;
        $status = AdminSendSMS::dispatchNow($sms); //馬上執行
        if($status['status'] == '傳送成功' || $status['status'] == '已送達業者'){
            return $this->appCodeResponse('Success', 0, '驗證碼已傳送。', 200);
        }else{
            return $this->appCodeResponse('Error', 99, '發送失敗。', 500);
        }
    }
}
