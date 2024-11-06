<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NewebPay;
use App\Jobs\AdminSendSMS;
use Validator;

class TestController extends Controller
{
    public function __construct()
    {
        $this->request = request();
        $this->Rules = [
            'type' => 'required|string|max:50',
            'user_id' => 'nullable|numeric',
            'phone' => 'required_if:type,smstest|max:20',
            'message' => 'required_if:type,smstest|string|max:20',
            'return' => 'nullable',
            'return' => 'nullable',
        ];
    }

    public function index()
    {
        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->Rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->Rules)->errors(), 400);
        }
        //將進來的資料作參數轉換(只取rule中有的欄位)
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->Rules))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }
        if($type == 'smstest'){
            !empty($data['return']) ? $data['return'] = true : $data['return'] = false;
            $status = AdminSendSMS::dispatchNow($data); //馬上執行
            return $this->debugResponse($status);
        }
        return $this->debugResponse('index');
    }

    public function store(Request $request)
    {
        return $this->debugResponse('store');
    }

    public function show($id)
    {
        return $this->debugResponse('show');
    }

    public function update(Request $request, $id)
    {
        return $this->debugResponse('update');
    }

    public function destroy($id)
    {
        return $this->debugResponse('destroy');
    }
}
