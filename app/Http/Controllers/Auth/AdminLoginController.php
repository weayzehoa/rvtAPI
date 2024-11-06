<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\Admin as AdminDB;
use App\Models\AdminPwdUpdateLog as AdminPwdUpdateLogDB;
use App\Models\AdminLoginLog as AdminLoginLogDB;
use DB;
use Hash;
use Session;
use App\Http\Requests\Admin\PasswordChangeRequest;
use App\Traits\GenerallyFunctionTrait;

class AdminLoginController extends Controller
{
    use GenerallyFunctionTrait;

    // 先經過 middleware 檢查
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => ['showPwdChangeForm','passwordChange','showLoginForm','logout']]);
    }

    // 顯示 admin.login form 表單視圖
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // 登入
    public function login(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account'   => 'required',
            'password' => 'required|min:6',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);

        $adminUser = AdminDB::where('account',$request->account)->first();

        if(!empty($adminUser)){
            $changeLog = AdminPwdUpdateLogDB::where('admin_id',$adminUser->id)
                ->select([DB::raw("DATEDIFF(NOW(),admin_pwd_update_logs.created_at) as last_modified")])
                ->orderBy('created_at','desc')->first();
            //直接撈資料表出來比對密碼方式
            $chkPassword = Hash::check($request->password, $adminUser->password);
            //檢查變更密碼是否超過90天
            if(empty($changeLog) || $changeLog->last_modified >= 90){
                // 轉至變更密碼表單
                return redirect()->to('passwordChange');
            }elseif($chkPassword){
                if($adminUser->lock_on <= 2){
                    $adminUser->update(['lock_on' => 0]);
                    Auth::guard('admin')->login($adminUser);
                    // 驗證無誤 記錄後轉入 dashboard
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => $adminUser->name.' 登入成功',
                        'ip' => $this->getRealIp(),
                    ]);
                    activity('後台管理')->causedBy($adminUser)->log('登入成功');
                    return redirect()->intended(route('admin.dashboard'));
                }else{
                    $message = '帳號已被鎖定！請聯繫管理員。';
                }
            }elseif($adminUser->is_on == 0){
                $message = '帳號已被停用！';
            }else{
                $adminUser->lock_on < 3 ? $adminUser->increment('lock_on') : '';
                $message = '帳號密碼錯誤！還剩 '.(3 - $adminUser->lock_on).' 次機會';
                $adminUser->lock_on >= 3 ? $message = '帳號已被鎖定！請聯繫管理員。' : '';
                if($adminUser->lock_on >= 3){
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => '密碼輸入錯誤三次，帳號鎖定。',
                        'ip' => $this->getRealIp(),
                    ]);
                }
            }
            return redirect()->back()->withInput($request->only('account', 'remember'))->withErrors(['account' => $message]);
        }
        $log = AdminLoginLogDB::create([
            'account' => $request->account,
            'result' => '登入失敗',
            'ip' => $this->getRealIp(),
        ]);
        // 驗證失敗 返回並拋出表單內容 只拋出 account 與 remember 欄位資料
        // 訊息 [使用者名稱或密碼錯誤] 為了不讓別人知道到底帳號是否存在
        return redirect()->back()->withInput($request->only('account', 'remember'))->withErrors(['account' => trans('auth.failed')]);
    }

    // 登出
    public function logout()
    {
        // 紀錄行為
        $adminuser = AdminDB::find(Auth::guard('admin')->id());
        activity('後台管理')->causedBy($adminuser)->log('登出成功');
        $log = AdminLoginLogDB::create([
            'admin_id' => $adminuser->id,
            'result' => '登出成功',
            'ip' => request()->ip(),
        ]);
        // 登出
        Auth::guard('admin')->logout();
        return redirect('/');
    }

    public function showPwdChangeForm()
    {
        return view('admin.change_password');
    }

    public function passwordChange(PasswordChangeRequest $request)
    {
        $admin = AdminDB::where([['account',$request->account],['is_on',1],['lock_on',0]])
            ->select([
                '*',
                'last_modified_pwd' => AdminPwdUpdateLogDB::whereColumn('admin_pwd_update_logs.admin_id','admins.id')
                    ->select('password')->orderBy('created_at','desc')->limit(1),
            ])->first();
        if(!empty($admin)){
            if(!Hash::check ($request->oldpass, $admin->password)){
                return redirect()->back()->withInput($request->only('account'))->withErrors(['oldpass' => '舊密碼輸入錯誤']);
            }elseif(Hash::check ($request->newpass, $admin->last_modified_pwd)){
                return redirect()->back()->withInput($request->only('account'))->withErrors(['oldpass' => '新密碼不可與上次修改的密碼相同']);
            }else{ //儲存新密碼並記錄
                $newPassWord = app('hash')->make($request->newpass);
                $admin->update(['password' => $newPassWord]);
                $log = AdminPwdUpdateLogDB::create([
                    'admin_id' => $admin->id,
                    'password' => $newPassWord,
                    'ip' => $this->getRealIp(),
                    'editor_id' => $admin->id,
                ]);
                Session::put('success','密碼已更新，請重新登入。');
                return redirect('/');
            }
        }
        return redirect()->back()->withErrors(['account' => '帳號不存在/禁用/鎖定。']);
    }
}
