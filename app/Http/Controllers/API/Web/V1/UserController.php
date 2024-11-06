<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\iCarryUserFavoriteProduct as UserFavoriteProductDB;
use App\Models\iCarryUserFavoriteVendor as UserFavoriteVendorDB;
use App\Models\iCarryReferCode as ReferCodeDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryShoppingCart as ShoppingCartDB;
use DB;
use Validator;
use Carbon\Carbon;
use App\Http\Requests\API\Web\V1\UserUpdateRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['api','refresh.token']);
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }elseif(!empty($this->request->icarry_uid)){
            $this->userId = $this->request->icarry_uid;
        }
        // $this->userId = 84533;
        $this->request = request();
        $request = $this->request;
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $this->showRules = [
            'type' => 'nullable|in:profile,orders,favorites,address,points',
            'lang' => 'nullable|in:en,jp,kr,th',
        ];
        $this->updateRules = [
            'type' => 'required|in:changePassword,editProfile',
            'currentPassword' => 'nullable|required_if:type,changePassword|min:4',
            'password' => 'nullable|required_if:type,changePassword|different:currentPassword|min:4',
            'passwordConfirm' => 'nullable|required_if:type,changePassword|different:currentPassword|same:password|min:4',
            'name' => 'required_if:type,editProfile|max:40',
            'email' => ['required_if:type,editProfile',
            function ($attribute, $value, $fail) {
                if(!empty($this->request->email)){
                    if(!preg_match("/^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$/", $this->request->email)){
                        $fail('E-mail 必須是有效的 E-mail。');
                    }
                }
            }
            ,'max:255'],
            'asiamiles_account' => 'nullable|digits:10',
            'refer_id' => 'nullable|max:11',
        ];
        $this->aesKey = env('APP_AESENCRYPT_KEY');
    }

    public function show($id)
    {
        // $id = 84533; //Roger測試 from icarry
        // $id = 4588; //信成測試 0955955706, 880513
        // $id = 72484; //非常多筆最愛
        // $id = 7785; //2萬多筆訂單
        // $id = 2020; //1000多筆訂單
        // $id = 6665; //100多筆訂單
        // $id = 81313; //20多筆訂單
        // $id = 14550; //240多筆地址
        // $id = 29999; //30多筆地址

        //認證的This->userId與$id不同時回應空值，避免盜用Auth擷取別人資料
        if ($this->userId != $id){
            return null;
        }

        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->showRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->showRules)->errors(), 400);
        }

        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->showRules))){
                $this->{$key} = $$key = $value;
            }
        }

        $user = UserDB::where('status',1);
        $user = $user->select([
            'id',
            'name',
            'nation',
            DB::raw("IF(mobile IS NULL,'',AES_DECRYPT(mobile,'$this->aesKey')) as mobile"),
        ]);

        if(isset($type)){
            if($type == 'favorites'){
                $user = $user->with('favoriteProducts');
            }
            if($type == 'profile'){
                $user = $user->addSelect([
                    'email',
                    'address',
                    DB::raw("(CASE WHEN refer_id is not null THEN refer_id ELSE refer_code END) as refer_id"),
                    'asiamiles_account',
                ]);
            }
            if($type == 'points'){
                $user = $user->addSelect([
                    'points',
                    'expiring_points' => UserPointDB::whereColumn('users.id', 'user_point.user_id')
                    ->where([['is_dead',0],['points','>',0],['dead_time','>',date('Y-m-d H:i:s')]])->select('points')->orderBy('dead_time','asc')->limit(1),
                    'points_dead_time' => UserPointDB::whereColumn('users.id', 'user_point.user_id')
                    ->where([['is_dead',0],['points','>',0],['dead_time','>',date('Y-m-d H:i:s')]])->select('dead_time')->orderBy('dead_time','asc')->limit(1),
                ]);
                $user = $user->with('pointsHistory');
            }
            if($type == 'address'){
                $user = $user->with('userAddress');
            }
            if($type == 'orders'){
                $user = $user->with('userOrders');
            }
        }

        $user = $user->findOrFail($this->userId);
        $user->mobile = mb_substr($user->mobile,0,3).'***'.mb_substr($user->mobile,-3);

        if(isset($type) && $type=='address'){
            if(count($user->userAddress) > 0){
                foreach($user->userAddress as $address){
                    $address->phone = mb_substr($address->phone,0,3).'***'.mb_substr($address->phone,-3);
                }
            }
        }
        return $this->appDataResponse('Success', 0, null, $user, 200);
    }

    public function update(Request $request, $id)
    {
        //認證的This->userId與$id不同時回應空值，避免盜用Auth擷取別人資料
        if ($this->userId != $id){
            return null;
        }
        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->updateRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->updateRules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->updateRules))){
                !empty($value) ? $this->{$key} = $$key = $data[$key] = $value : '';
            }
        }
        //找出已驗證過的使用者資料
        $user = UserDB::where('status',1)->select(['id','password','refer_id','refer_code','points'])->find($id);
        if(!empty($user)){
            if($type == 'changePassword'){
                //舊密碼比對及新密碼處理
                if(!empty($data['currentPassword'])){
                    if($user->password == sha1($data['currentPassword'])){
                        !empty($data['password']) ? $data['pwd'] = $data['password'] = sha1($data['password']) : '';
                    }else{
                        return $this->appCodeResponse('Error', 999, ['currentPassword' => '輸入舊碼錯誤'], 400);
                    }
                }
                unset($data['name']);
                unset($data['email']);
                unset($data['asiamiles_account']);
                unset($data['refer_id']);
                unset($data['currentPassword']);
                unset($data['passwordConfirm']);
            }
            if($type == 'editProfile'){
                unset($data['currentPassword']);
                unset($data['password']);
                unset($data['passwordConfirm']);
                //推薦碼
                if(!empty($data['refer_id'])){
                    if($data['refer_id'] == $id){
                        return $this->appCodeResponse('Error', 1, '推薦碼無法填寫自己代碼', 400);
                    }
                    if(!empty($user->refer_id) || !empty($user->refer_code)){ //已經有推薦碼
                        return $this->appCodeResponse('Error', 2, '推薦碼只能使用一次', 400);
                    }
                    if(empty($user->refer_id) && empty($user->refer_code)){ //獲得購物金處理
                        //如果是數字檢驗是否為經過驗證的使用者
                        if(is_numeric($data['refer_id'])){
                            return $chk = UserDB::where([['id',$data['refer_id']],['status',1]])->select(['id','points'])->first();
                        }else{ //非數字檢查是否為推薦碼
                            $data['refer_code'] = $data['refer_id'];
                            $data['refer_id'] = null;
                            $chk = ReferCodeDB::where([['code',$data['refer_code']],['status',1]])
                                ->where([['start_time','<=',date('Y-m-d')],['end_time','>=',date('Y-m-d')]])->first();
                        }
                        if(isset($chk)){
                            $userPoint['user_id'] = $id;
                            if(isset($data['refer_code'])){
                                $points = $chk->icarry_point;
                                $chk->increment('total_register');
                                $userPoint['point_type'] = "$chk->code 註冊推薦，贈送 $points 點";
                            }else{
                                $points = 100;
                                $referId = $data['refer_id'];
                                $userPoint['point_type'] = "獲得 $referId 推薦，贈送 $points 點";
                            }
                            $data['points'] = $user->points + $points;
                            // $userPoint['from_user_id'] = $data['refer_id'];
                            $userPoint['points'] = $points;
                            $userPoint['balance'] = $data['points'];
                            $userPoint['dead_time'] = Carbon::now()->addDays(180);
                            $userPoint = UserPointDB::create($userPoint);
                        }else{
                            return $this->appCodeResponse('Error', 3, '推薦碼不存在或已失效', 400);
                        }
                    }
                }
            }
            //更新user資料
            if($type == 'changePassword' || $type == 'editProfile'){
                $user->update($data);
                return $this->appCodeResponse('Success', 0, '更新成功。', 200);
            }
        }else{
            return $this->appCodeResponse('Error', 0, '使用者資料不存在。', 400);
        }
        return null;
    }
}
