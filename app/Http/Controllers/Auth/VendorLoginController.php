<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\VendorAccount as VendorAccountDB;

class VendorLoginController extends Controller
{
    // 先經過 middleware 檢查
    public function __construct()
    {
        $this->middleware('guest:vendor', ['except' => ['showLoginForm','logout']]);
    }

    // 顯示 vendor login form 表單視圖
    public function showLoginForm()
    {
        $account = request()->account;
        $icarryToken = request()->icarryToken;
        // 直接從iCarry後台過來不檢查商家或帳號是否啟用
        if(!empty($account) && !empty($icarryToken)){
            $adminUser = VendorAccountDB::where([['account',$account],['icarry_token',$icarryToken]])->first();
            if (!empty($adminUser)) {
                //登入
                auth('vendor')->login($adminUser);
                //紀錄
                activity('商家後台管理')->causedBy($adminUser)->log('登入成功');
                // 驗證無誤轉入 dashboard
                return redirect()->intended(route('vendor.dashboard'));
            }
        }
        return view('vendor.login');
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

        // 檢驗帳號密碼權限及商家是否開啟
        $password = sha1($request->password);
        $checkVendor = VendorAccountDB::join('vendors','vendors.id','vendor_accounts.vendor_id')
            ->where([
                ['vendor_accounts.account',$request->account],
                ['vendor_accounts.password',$password],
                ['vendor_accounts.is_on',1],
                ['vendor_accounts.shop_admin',1],
                ['vendors.is_on',1],
            ])->first();

        // 資料存在通過檢驗，將帳號密碼送去guard驗證登入
        if (!empty($checkVendor)) {
            if (Auth::guard('vendor')->attempt(['account' => $request->account, 'password' => $request->password, 'is_on' => 1, 'shop_admin' => 1])) {
                $vendorAdmin = VendorAccountDB::find(Auth::guard('vendor')->id());
                activity('商家後台管理')->causedBy($vendorAdmin)->log('登入成功');
                // 驗證無誤轉入 dashboard
                return redirect()->intended(route('vendor.dashboard'));
            }
        }

        // 驗證失敗 返回並拋出表單內容 只拋出 account 欄位資料
        // 只顯示訊息 [使用者名稱、密碼錯誤或無權限] 為了不讓別人知道到底商家或帳號是否存在
        return redirect()->back()->withInput($request->only('account'))->withErrors(['account' => trans('auth.failed')]);
    }

    // 登出
    public function logout()
    {
        // 紀錄行為
        $adminuser = VendorAccountDB::find(Auth::guard('vendor')->id());
        activity('商家後台管理')->causedBy($adminuser)->log('登出成功');

        // 登出
        Auth::guard('vendor')->logout();
        return redirect('/');
    }
}
