<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NewebPay;
use App\Traits\NewebPayTrait;
use App\Models\Order as OrderDB;

class PayTestController extends Controller
{
    use NewebPayTrait;
    protected $payProvider = ['智付通信用卡' => 'spgateways', '智付通ATM' => 'spgateways', '智付通CVS' => 'spgateways', '智付通銀聯卡' => 'spgateways'];

    public function __construct()
    {
        $this->pay_method = request()->pay_method;
        $this->order_number = request()->order_number;
        $this->orderNumber = time().'TS';
        $this->totalAmount = 2000;
        $this->description = 'iCarry我來寄 訂單';
        $this->buyerEmail = 'roger@icarry.me';
    }

    public function index()
    {
        if(in_array($this->pay_method,array_keys($this->payProvider))){
            //建一筆假訂單
            $order = OrderDB::create([
                'user_id' => 84533,
                'order_number' => $this->orderNumber,
                'origin_country' => '台灣',
                'from' => 1,
                'to' => 1,
                'ship_to' => '台灣',
                'pay_method' => $this->pay_method,
                'receiver_name' => 'Roger Wu',
                'shipping_method' => 4,
                'invoice_type' => 2,
                'amount' => 1800,
                'invoice_sub_type' => 1,
                'love_code' => 12345,
                'status' => 0,
                'buyer_email' => 'roger@icarry.me',
            ]);
            $provider = $this->payProvider[$this->pay_method];

            if($provider == 'spgateways'){
                $this->form = $this->newebPay($this->pay_method,$this->orderNumber,$this->totalAmount,$this->buyerEmail);
            }
            if(!empty($this->form)){
                return redirect()->route('pay.index', ['pay' => $this->form]);
            }
        }
        $this->message = '付款方式錯誤。';
        if(!empty($this->message)){
            return redirect()->route('index', ['message' => $this->message]);
        }
    }

    public function newebpayCancel()
    {
        if(!empty($this->order_number)){
            $cancel = $this->newebpayCreditCancel($this->order_number);
            return $cancel;
        }else{
            $this->message = '輸入訂單號碼';
        }

        if(!empty($this->message)){
            return redirect()->route('index', ['message' => $this->message]);
        }

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
