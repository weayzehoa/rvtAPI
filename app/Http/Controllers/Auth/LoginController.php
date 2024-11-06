<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\User as UserDB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    //登入
    public function login(Request $request)
    {
        // dd($request);
        // 驗證表單資料
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:4',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);
        // 將表單資料送去Auth::gurard()驗證
        if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            //登入成功紀錄
            $user = UserDB::find(Auth::guard()->id());
            activity('前台會員')->causedBy($user)->log('登入成功');

            //驗證無誤轉入 登入前那一頁
            // return redirect()->intended();
            //登入到Home頁面
            return redirect()->route('home');
        }
        // 驗證失敗 返回並拋出表單內容 只拋出 email 與 remember 欄位資料,
        // 訊息 [使用者名稱或密碼錯誤] 為了不讓別人知道到底帳號是否存在
        return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['email' => trans('auth.failed')]);
    }
    //登出
    public function logout()
    {
        //登出成功紀錄
        $user = UserDB::find(Auth::guard()->id());
        activity('前台會員')->causedBy($user)->log('登出成功');

        //清除紀錄並轉向回 首頁
        Auth::guard()->logout();
        return redirect()->route('index');
    }
}
