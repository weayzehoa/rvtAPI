<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order as OrderDB;
use App\Models\User as UserDB;
use App\Models\UserPoint as UserPointDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\TashinCreditcard as TashinCreditcardDB;
use App\Traits\NewebPayTrait;
use DB;
use Carbon\Carbon;

class PayCallBackController extends Controller
{
    use NewebPayTrait;

    //NewebPay 即時交易返回
    public function acpayNotify(Request $request)
    {
        return 'SUCCESS';
    }

    public function newebpayReturn(Request $request)
    {
        if(!empty($request->all())){
            $tradInfo = $this->newebDecrypt($request->input('TradeInfo'));
            $resultJson = json_decode($tradInfo,true);
            $result = $resultJson['Result'];
            $orderNumber = $result['MerchantOrderNo'];
            if($request->Status == 'SUCCESS'){
                $memo = '付款交易成功';
                $payStatus = 1;
            }else{
                $memo = $this->newebPayCode($request->Status);
                $payStatus = -1;
            }
            //紀錄
            $log = $this->newebLog($orderNumber,$payStatus,$tradInfo,$resultJson,$memo,$type = 'update');
            //找出該筆訂單及相關使用資料
            $order = OrderDB::join('users','users.id','orders.user_id')
                    ->where('order_number',$orderNumber)
                    ->select([
                        'orders.id',
                        'orders.order_number',
                        'orders.status',
                        'users.id as user_id',
                        DB::raw('(CASE WHEN refer_id != "" or refer_id != 0 THEN refer_id ELSE null END) as refer_id')
                    ])->first();
            $this->orderId = $order->id;
            $this->userId = $order->user_id;
            $this->referId = $order->refer_id;
            if($payStatus == 1 && $order->status == 0){ //訂單狀態為0才更新
                //更新訂單狀態
                $order->update(['status' => 1, 'pay_time' => date('Y-m-d H:i:s')]);
                //檢查使用者是否第一次訂購,推薦者獲得購物金
                $this->checkFirstOrder();
            }
            // 重新導向前台
            if(env('APP_ENV') == 'production'){
                // return redirect(); //等正式頁面
            }else{
                return redirect()->route('payTest', ['order_id' => $this->orderId]); //金流測試頁面
            }
        }
        return null;
    }

    //NewebPay 非即時交易取號
    public function newebpayGetCode(Request $request)
    {
        if (!empty($request->all())) {
            $tradInfo = $this->newebDecrypt($request->input('TradeInfo'));
            $resultJson = json_decode($tradInfo, true);
            $result = $resultJson['Result'];
            $orderNumber = $result['MerchantOrderNo'];
            //只需要紀錄起來, 然後返回訂單資訊給前端
            $payStatus = 2;
            $memo = '取號完成';
            $log = $this->newebLog($orderNumber, $payStatus, $tradInfo, $resultJson, $memo, $type = 'update');
            $order = OrderDB::where('order_number',$orderNumber)->select('id')->first();
            if(!empty($order)){
                $this->orderId = $order->id;
            }
            // 重新導向前台
            if(env('APP_ENV') == 'production'){
                // return redirect(); //等正式頁面
            }else{
                return redirect()->route('payTest', ['order_id' => $this->orderId]); //金流測試頁面
            }
        }
        return null;
    }

    //NewebPay 非即時交易返回
    public function newebpayNotify(Request $request)
    {
        if(!empty($request->all())){
            $tradInfo = $this->newebDecrypt($request->input('TradeInfo'));
            $resultJson = json_decode($tradInfo,true);
            $result = $resultJson['Result'];
            $orderNumber = $result['MerchantOrderNo'];
            if($resultJson['Status'] == 'SUCCESS'){
                $memo = '付款完成';
                $payStatus = 1;
            }else{
                $memo = $this->newebPayCode($resultJson['Status']);
                $payStatus = -1;
            }
            //紀錄
            $log = $this->newebLog($orderNumber,$payStatus,$tradInfo,$resultJson,$memo,$type = 'update');
            //找出該筆訂單及相關使用資料
            $order = OrderDB::join('users','users.id','orders.user_id')
                    ->where('order_number',$orderNumber)
                    ->select([
                        'orders.id',
                        'orders.order_number',
                        'orders.status',
                        'users.id as user_id',
                        DB::raw('(CASE WHEN refer_id != "" or refer_id != 0 THEN refer_id ELSE null END) as refer_id'),
                    ])->first();
            $this->orderId = $order->id;
            $this->userId = $order->user_id;
            $this->referId = $order->refer_id;
            //更新訂單狀態
            if($payStatus == 1 && $order->status == 0){ //訂單狀態為0才更新
                $order = update(['status' => 1, 'pay_time' => date('Y-m-d H:i:s')]);
            }
            //檢查使用者是否第一次訂購,推薦者獲得購物金
            $this->checkFirstOrder();
            //不通知前端，返回 true 給智付通代表接收完成
            return true;
        }
        return null;
    }

    //玉山支付寶
    public function esunAlipayNotify(Request $request)
    {
        if (!empty($request)) {
            //laravel 使用此方法解request來的json檔案, 待正式時需要測試一下是否正確.
            $decode = $request->json()->all();
            // $decode = json_decode(urldecode($request), true);
            $TransactionData = json_decode(urldecode($decode['TransactionData']), true);
            $HashDigest = hash('sha256', urldecode($decode['TransactionData']).'13bf1dc8550f1f9a537804ced7dd76f7b0c27a617a542dc9e4314f49c3f15ab8');
            if ($HashDigest == $decode['HashDigest']) {
                $OrderNo = $TransactionData['OrderNo'];
                $WalletOrderNo = $TransactionData['WalletOrderNo'];
                $alipay = AlipayDB::where('order_number',$OrderNo)->update([
                    'get_json' => json_encode($TransactionData),
                    'pay_status' => 1,
                    'payment_number' => $WalletOrderNo,
                ]);
                $order = OrderDB::where('order_number',$OrderNo)->first();
                if(!empty($order)){
                    $this->orderId = $order->id;
                    $this->userId = $order->user_id;
                    $this->referId = $order->refer_id;
                    //處理狀態為0的訂單
                    if($order->status == 0){
                        //發送推播通知
                        // $api_result=icarry_notify('iCarry訂單管理', '訂單付款成功', 'tw', $OrderNo);
                        //信件通知
                        $mail['type'] = 'alipayNotice'; //信件類別
                        $mail['to'] = [$order->user->email]; //需使用陣列
                        $mail['subject'] = "iCarry订单#{$order->order_number}订购成功通知";
                        $mail['data'] = $order; //製作Body的資料
                        $mail['admin_id'] = 0; //返回沒有管理者id=0
                        $result = AdminSendEmail::dispatchNow($mail); //馬上執行
                        //更新訂單狀態
                        $order->update(['status' => 1, 'pay_time' => date('Y-m-d H:i:s')]);
                    }
                    //檢查使用者是否第一次訂購,推薦者獲得購物金
                    $this->checkFirstOrder();
                    return 'success';
                }
            }
        }
    }

    //台新POST Back (使用get方式)
    public function tashinPostBack()
    {
        $get = request()->all();
        if(!empty($get->order_no)){
            $order = OrderDB::where('order_number',$get->order_no)->first();
        }
        if(!empty($order)){
            $this->orderId = $order->id;
            $this->userId = $order->user_id;
            $this->referId = $order->refer_id;
            empty($order->discount) ? $order->discount = 0 : '';
            $realAmount = $order->amount - $order->spend_point + $order->shipping_fee + $order->parcel_tax - $order->discount;
            $api = env('TASHIN_UNION_RESET_API_URL');
            $post = [
                'sender' => 'rest',
                'ver' => '1.2.0',
                'mid' => env('TASHIN_UNION_MerchantID'),
                's_mid' => $get->s_mid,
                'tid' => env('TASHIN_UNION_TerminalID'),
                'pay_type' => 2,
                'tx_type' => 7,
                'params' => [
                    'order_no' => $order->order_number,
                    'tx_type' => 7,
                    'result_flag' => 1
                ],
            ];
            $postJson = json_encode($post,JSON_UNESCAPED_UNICODE);
            $result = tsc_post($api,$postJson);
            $ret = json_decode($result,true);
            /*
            $ret = Array ( [ver] => 1.2.0 [mid] => 000812770025776 [tid] => T0000000 [pay_type] => 2 [tx_type] => 7 [ret_value] => 0 [params] => Array ( [ret_code] => 00 [order_status] => 12 [cur] => NTD [tx_amt] => 30700 [qid] => 911910141453590338868 [purchase_date] => 2019-10-14 14:53:54 [settle_amt] => 0 [refund_amt] => 0 ) )
            */
            if($ret["params"]["ret_code"]=="00"){//查詢成功
                if($ret["params"]["order_status"]=="02"){//02 已授權
                    $tsc = TashinCreditcardDB::where('order_number',$order->order_number)->first();
                    $tsc = $tsc->update([
                        'get_json' => json_encode($ret,JSON_UNESCAPED_UNICODE),
                        'pay_status' => 1,
                    ]);
                    if($order->status == 0){
                        $order = $order->update([
                            'status' => 1,
                            'pay_time' => date('Y-m-d H:i:s'),
                        ]);
                        //檢查使用者是否第一次訂購,推薦者獲得購物金
                        $this->checkFirstOrder();
                        // $api_result=icarry_notify("iCarry訂單管理","訂單付款成功","tw",$order_number);
                    }
                    // 重新導向前台
                    if(env('APP_ENV') == 'production'){
                        // return redirect(); //等正式頁面
                    }else{
                        return redirect()->route('payTest', ['order_id' => $order->id]); //金流測試頁面
                    }
                }else{//已請款,已取消,已退貨....等
                    $order = $order->update([
                        'status' => 0,
                    ]);
                    // 重新導向前台付款失敗頁面
                    if(env('APP_ENV') == 'production'){
                        // return redirect(); //等正式頁面
                    }else{
                        return redirect()->route('payTest', ['order_id' => $order->id]); //金流測試頁面
                    }
                }
            }else{//失敗$ret["params"]["ret_msg"]
                // 重新導向前台付款失敗頁面
                if(env('APP_ENV') == 'production'){
                    // return redirect(); //等正式頁面
                }else{
                    return redirect()->route('payTest', ['order_id' => $order->id]); //金流測試頁面
                }
            }
        }
    }

    //台新Result Back (使用POST方式)
    public function tashinResultBack(Request $request)
    {
        //laravel 使用此方法解request來的json檔案, 待正式時需要測試一下是否正確.
        $ret = $request->json()->all();
        if(!empty($ret)){
            $order = OrderDB::where('order_number',$ret['params']['order_no'])->first();
            $tsc = TashinCreditcardDB::where('order_number',$ret['params']['order_no'])->first();
            if(!empty($order) && !empty($tsc)){
                $this->orderId = $order->id;
                $this->userId = $order->user_id;
                $this->referId = $order->refer_id;
                if($ret["params"]["ret_code"]=="00"){//成功
                    $tsc = $tsc->update([
                        'get_json' => json_encode($ret,JSON_UNESCAPED_UNICODE),
                        'pay_status' => 1,
                    ]);
                    if($order->status == 0){
                        $order = $order->update([
                            'status' => 1,
                            'pay_time' => date('Y-m-d H:i:s'),
                        ]);
                        //檢查使用者是否第一次訂購,推薦者獲得購物金
                        $this->checkFirstOrder();
                        // $api_result=icarry_notify("iCarry訂單管理","訂單付款成功","tw",$order_number);
                    }
                }else{//失敗$ret["params"]["ret_msg"]
                    $tsc = $tsc->update([
                        'get_json' => json_encode($ret,JSON_UNESCAPED_UNICODE),
                        'pay_status' => 0,
                    ]);
                }
                // 重新導向前台
                if(env('APP_ENV') == 'production'){
                    // return redirect(); //等正式頁面
                }else{
                    return redirect()->route('payTest', ['order_id' => $order->id]); //金流測試頁面
                }
            }
        }
    }

    /**
     * 檢查使用者與推薦者關聯是否為第一次訂購
     * 某使用者沒有輸入 refer_id 已經成功購買過一次, 然後過一個月後他輸入 refer_id 又再次購買商品成功,
     * 這時候要不要送給 refer_id 購物金?? 要.
     *
    */
    private function checkFirstOrder()
    {
        if(!empty($this->referId)){
            $check = UserPointDB::where([['user_id',$this->referId],['from_user_id',$this->userId]])->first();
            if(empty($check)){
                $user = UserDB::find($this->referId);
                $data['points'] = 100;
                $data['user_id'] = $this->referId;
                $data['from_user_id'] = $this->userId;
                $data['balance'] = $user->points + $data['points'];
                $data['point_type'] = "推薦 $this->userId 成功，贈送 100 點";
                $data['dead_time'] = Carbon::now()->addDays(180);
                $user = $user->update(['points' => $data['balance']]);
                UserPointDB::create($data);
            }
        }
    }
}
