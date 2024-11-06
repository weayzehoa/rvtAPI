<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\Admin as AdminDB;

class GateLoginController extends Controller
{
    // 先經過 middleware 檢查
    public function __construct()
    {
        $this->middleware('guest:gate', ['except' => ['showLoginForm','logout']]);
    }

    // 顯示 gate.login form 表單視圖
    public function showLoginForm()
    {
        return view('gate.login');
    }

    // 登入
    public function login(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account'   => 'required',
            'password' => 'required|min:4',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);

        // 將表單資料送去guard驗證
        if (Auth::guard('gate')->attempt(['account' => $request->account, 'password' => $request->password, 'is_on' => 1], $request->remember)) {

            // 紀錄行為
            $adminuser = AdminDB::find(Auth::guard('gate')->id());
            activity('中繼後台管理')->causedBy($adminuser)->log('登入成功');

            // 驗證無誤轉入 dashboard
            return redirect()->intended(route('gate.dashboard'));
        }

        // 驗證失敗 返回並拋出表單內容 只拋出 account 與 remember 欄位資料
        // 訊息 [使用者名稱或密碼錯誤] 為了不讓別人知道到底帳號是否存在
        return redirect()->back()->withInput($request->only('account', 'remember'))->withErrors(['account' => trans('auth.failed')]);
    }

    // 登出
    public function logout()
    {
        // 紀錄行為
        $adminuser = AdminDB::find(Auth::guard('gate')->id());
        activity('後台管理')->causedBy($adminuser)->log('登出成功');

        // 登出
        Auth::guard('gate')->logout();
        return redirect('/');
    }
}
