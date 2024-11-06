<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUserFavorite as UserFavoriteDB;
use Validator;

class UserFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['api','refresh.token']);
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
        }elseif(!empty($this->request->icarry_uid)){
            $this->userId = $this->request->icarry_uid;
        }else{
            $this->UserId = null;
        }
        $this->request = request();
        $this->rules = [
            'id' => 'required|numeric',
            'type' => 'required|string|in:vendor,product',
        ];
        $this->delRules = [
            'type' => 'required|string|in:vendor,product',
        ];
        foreach ($this->request->all() as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function store(Request $request)
    {
        if (Validator::make($this->request->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->rules)->errors(), 400);
        }
        $favorite = UserFavoriteDB::where([['user_id',$this->userId],['table_id',$this->id],['table_name',$this->type]])->first();
        if(empty($favorite)){
            $favorite = UserFavoriteDB::create([
                'user_id' => $this->userId,
                'table_id' => $this->id,
                'table_name' => $this->type,
            ]);
            return $this->appCodeResponse('Success', 0, '新增成功。', 200);
        }else{
            return $this->appCodeResponse('Error', 0, '已存在。', 200);
        }
    }

    public function destroy($id)
    {
        if (Validator::make($this->request->all(), $this->delRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->delRules)->errors(), 400);
        }

        $favorite = UserFavoriteDB::where([['user_id',$this->userId],['table_id',$id],['table_name',$this->type]])->first();

        if(!empty($favoriteProduct)){
            $favoriteProduct->delete();
            return $this->appCodeResponse('Success', 0, '移除成功。', 200);
        }else{
            return $this->appCodeResponse('Error', 0, '資料不存在。', 404);
        }
    }
}
