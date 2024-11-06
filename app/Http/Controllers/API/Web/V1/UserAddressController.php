<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUserAddress as UserAddressDB;
use App\Models\iCarryCountry as CountryDB;
use Validator;
use File;
use Storage;
use Spatie\Image\Image as Image;
use DB;

class UserAddressController extends Controller
{
    protected $userId;

    protected $indexRules = [
        'to_country_id' => 'nullable|numeric',
    ];

    protected $rulesForUSA = [
        'name' => 'required|max:40|regex:/^[a-zA-Z0-9\.\,\-\s]+$/',
        'country' => 'required|max:20',
        'address' => 'required|max:255|regex:/^[a-zA-Z0-9\.\,\-\s\[\]\(\)\{\}\~\@\#\$\%\^\&\*\_\<\>\;\/\:\=\`]+$/',
        'email' => 'required|email|max:255',
        'zip_code' => 'required|string|regex:/^[+o0-9]+$/|max:10',
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:3',
        'phone' => 'required|numeric',
        'is_default' =>'nullable|numeric|max:1',
        'china_id_img1' => 'nullable|image',
        'china_id_img2' => 'nullable|image',
    ];

    protected $rules = [
        'name' => 'required|max:40',
        'country' => 'required|max:20',
        'city' => 'required_if:country,台灣,中國,香港|max:20',
        'area' => 'required_if:country,台灣,中國,香港|max:20',
        's_area' => 'required_if:country,中國|max:20',
        'address' => 'required|max:255',
        'email' => 'required|email|max:255',
        'zip_code' => 'required|string|regex:/^[+o0-9]+$/|max:10',
        'nation' => 'required|string|regex:/^[+o0-9]+$/|max:3',
        'phone' => 'required|numeric',
        'is_default' =>'nullable|numeric|max:1',
        'china_id_img1' => 'nullable|image',
        'china_id_img2' => 'nullable|image',
    ];

    public function __construct()
    {
        $this->imageUrl = env('AWS_CHINA_ID_IMAGE_FILE_URL');
        $this->middleware(['optimizeImages','api','refresh.token']);
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }elseif(!empty($this->request->icarry_uid)){
            $this->userId = $this->request->icarry_uid;
        }
        $this->aesKey = env('APP_AESENCRYPT_KEY');
        // $this->userId = 84533; //Roger
        // $this->userId = 4588; //信成
    }

    public function index()
    {
        //驗證失敗返回訊息
        if (Validator::make(request()->all(), $this->indexRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->indexRules)->errors(), 400);
        }
        if(!empty($this->userId)){
            $userAddress = UserAddressDB::where('user_id',$this->userId);
            if(!empty(request()->to_country_id)){
                $tmp = CountryDB::find(request()->to_country_id);
                if(!empty($tmp)){
                    $userAddress = $userAddress->where('country',$tmp->name);
                }else{
                    return $this->appCodeResponse('Error', 1, ['to_country_id' => '國家id不存在'], 400);
                }
            }
            $userAddress = $userAddress->select([
                'id',
                'user_id',
                'name',
                'nation',
                // 'phone',
                DB::raw("IF(phone IS NULL,'',AES_DECRYPT(phone,'$this->aesKey')) as phone"),
                'country',
                'city',
                'area',
                's_area',
                'address',
                'china_id_img1', //資料內含完整網址
                'china_id_img2', //資料內含完整網址
                'is_default',
            ]);
            $userAddress = $userAddress->orderBy('country','asc')->orderBy('is_default','desc')->orderBy('id','desc')->get();
            foreach($userAddress as $address){
                if(!empty($address->phone)){
                    $address->phone = mb_substr($address->phone,0,3).'***'.mb_substr($address->phone,-3);
                }
            }
            return $this->successResponse($userAddress->count(),$userAddress);
        }
        return null;
    }

    public function store(Request $request)
    {
        //規則切換
        if($request->country == '美國' || $request->country == '韓國' || $request->country == '南韓'){
            $rules = $this->rulesForUSA;
        }else{
            $rules = $this->rules;
        }
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $rules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($rules))){
                if($key == 'phone'){
                    $data[$key] = ltrim($value,'0');
                }else{
                    $data[$key] = $value;
                }
            }
        }
        //unset空值變數(避免其它欄位被異動)
        foreach ($data as $key => $value) {
            if(empty($value)){
                unset($data[$key]);
            }
        }
        //處理預設
        empty($data['is_default']) ? $data['is_default'] = 0 : '';
        if($data['is_default'] == 1){
            UserAddressDB::where([['user_id',$this->userId],['country',$data['country']]])->update(['is_default' => 0]);
        }
        //延遲30秒時間,避免連續新增
        $delay = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))-30);
        $chk = UserAddressDB::where([['user_id',$this->userId],['create_time','>=',$delay]])->select('id')->orderBy('create_time','desc')->first();
        if(isset($chk)){
            return $this->appCodeResponse('Error', 9, '發送過於頻繁，請稍後再試。', 400);
        }
        if($request->hasFile('china_id_img1') || $request->hasFile('china_id_img2')){
            if($request->hasFile('china_id_img1')){
                $data['china_id_img1'] = $this->imageUrl.'/'.$this->storeFile($request->file('china_id_img1'));
            }
            sleep(2); //延遲2秒避免速度太快變成同檔名
            if($request->hasFile('china_id_img2')){
                $data['china_id_img2'] = $this->imageUrl.'/'.$this->storeFile($request->file('china_id_img2'));
            }
        }
        //新增
        $data['user_id'] = $this->userId;
        isset($data['phone']) ? $phone = $data['phone'] : $phone = null;
        !empty($phone) ? $data['phone'] = DB::raw("AES_ENCRYPT('$phone', '$this->aesKey')") : '';
        $userAddress = UserAddressDB::create($data);
        return $this->appCodeResponse('Success', 0, '新增成功。', 200);
    }

    public function show($id)
    {
        $userAddress = UserAddressDB::where('user_id',$this->userId)->select([
            'id',
            'user_id',
            'name',
            'nation',
            // 'phone',
            DB::raw("IF(phone IS NULL,'',AES_DECRYPT(phone,'$this->aesKey')) as phone"),
            'country',
            'city',
            'area',
            's_area',
            'address',
            'china_id_img1', //資料內含完整網址
            'china_id_img2', //資料內含完整網址
            'is_default',
        ])->findOrFail($id);

        return $this->dataResponse($userAddress, 'user_address', $id);
    }

    public function update(Request $request, $id)
    {
        if($request->country == '美國' || $request->country == '韓國' || $request->country == '南韓'){
            $rules = $this->rulesForUSA;
        }else{
            $rules = $this->rules;
        }
        //驗證失敗返回訊息
        if (Validator::make($request->all(), $rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $rules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($request->all() as $key => $value) {
            if(in_array($key, array_keys($rules))){
                if($key == 'phone'){
                    $data[$key] = ltrim($value,'0');
                }else{
                    $data[$key] = $value;
                }
            }
        }
        //unset空值變數(避免其它欄位被異動)
        foreach ($data as $key => $value) {
            if(empty($value)){
                unset($data[$key]);
            }
        }
        //找出原始資料
        $userAddress = UserAddressDB::where('user_id',$this->userId)->findOrFail($id);
        //處理預設
        empty($data['is_default']) ? $data['is_default'] = 0 : '';
        if($data['is_default'] == 1){
            UserAddressDB::where([['user_id',$this->userId],['country',$data['country']]])->update(['is_default' => 0]);
        }
        if($request->hasFile('china_id_img1') || $request->hasFile('china_id_img2')){
            if($request->hasFile('china_id_img1')){
                $data['china_id_img1'] = $this->imageUrl.'/'.$this->storeFile($request->file('china_id_img1'));
            }
            sleep(2); //延遲2秒避免速度太快變成同檔名
            if($request->hasFile('china_id_img2')){
                $data['china_id_img2'] = $this->imageUrl.'/'.$this->storeFile($request->file('china_id_img2'));
            }
        }
        //更新
        isset($data['phone']) ? $phone = $data['phone'] : $phone = null;
        !empty($phone) ? $data['phone'] = DB::raw("AES_ENCRYPT('$phone', '$this->aesKey')") : '';
        $userAddress->update($data);
        return $this->appCodeResponse('Success', 0, '更新成功。', 200);
    }

    public function destroy($id)
    {
        $userAddress = UserAddressDB::where('user_id',$this->userId)->select('id')->findOrFail($id);
        $userAddress->delete();
        return $this->appCodeResponse('Success', 0, '刪除成功。', 200);
    }

    private function storeFile($file){
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName = date('YmdHis') . '.' . $ext;
        //將檔案搬至本地目錄
        $file->move(public_path(), $fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().'/'.$fileName)
        ->width(1440)
        ->height(720)
        ->save(public_path().'/'.$fileName);
        //將檔案傳送至 S3
        //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
        Storage::disk('s3ChinaIdImage')->put($fileName, file_get_contents(public_path().'/'.$fileName) , 'public');
        //刪除本地檔案
        unlink(public_path().'/'.$fileName);
        return $fileName;
    }
}
