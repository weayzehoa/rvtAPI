<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Curl;

class EsunCardlinkController extends Controller
{
    protected $sid;
    protected $key;
    protected $keyPay;
    protected $userId;
    protected $eSunUrl = [
        "verify"=>"https://card.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/commVerify",
        "register"=>"https://card.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/rgstACC",
        "getTkey"=>"https://card.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/tknService",
        "delete"=>"https://card.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/cancelLink",
        "query"=>"https://card.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/linkQuery",
        "pay"=>"https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf013c"
    ];
    protected $testUrl = [
        "verify"=>"https://cardtest.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/commVerify",
        "register"=>"https://cardtest.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/rgstACC",
        "getTkey"=>"https://cardtest.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/tknService",
        "delete"=>"https://cardtest.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/cancelLink",
        "query"=>"https://cardtest.esunbank.com.tw/EsunCreditweb/txnproc/cardLink/linkQuery",
        "pay"=>"https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf013c"
    ];
    public function __construct()
    {
        if(env('APP_ENV') == 'production'){
            $this->middleware(['api','refresh.token'])->only(['register','cancel']);
            if(auth('webapi')->check()){
                $this->userId = auth('webapi')->user()->id;
            }elseif(!empty($this->request->icarry_uid)){
                $this->userId = $this->request->icarry_uid;
            }
            $this->sid = "8080836856";
            $this->key = "0258F825C53A05Z497B64662B3260BB9";//玉山網路收單系統-帳號通知信-8080836856
            $this->keyPay = "1V1VO1ZCKOGVSH1GAF7RHDPFJJMJWB3U";//玉山網路收單系統-帳號通知信-8080836856
            $this->url = $this->eSunUrl;
        }else{
            $this->sid = "8089027225";//測試
            $this->key = "D67B1C83A593924A46D777B913D1C221";//測試
            $this->url = $this->testUrl;
            $this->userId = 84533;
        }
    }

    public function register()
    {
        $txToken=$this->getTxToken();
        return $this->debugResponse($txToken);
    }

    public function cancel()
    {
        return $this->debugResponse('register');
    }

    //取得一次性token
    protected function getTxToken(){
        $array=[
            "TxnTp" => "V1",
            "SID" => $this->sid,
            "SKey" => $this->userId,
        ];
        $data=json_encode($array,JSON_UNESCAPED_UNICODE);
        $mac=hash('sha256', "{$data}{$this->key}");
        $ksn="1";
        $str="data={$data}&mac={$mac}&ksn={$ksn}";
        $url = $this->url['verify'];
        $result = $this->cardlinkPost($url,$str);
        return $result;
        if(!empty($result)){
            $json=json_decode($result,true);
            if($json["RtnCD"]=="00"){
                return $json["txToken"];
            }
        }
        return '';
    }

    //註冊作業(特店->玉山)
    function cardlinkRegister($rData=''){
        $array=array(
            "TxnTp"=>"A1",
            "SID"=>$this->sid,
            "SKey"=>$this->userId,
            "txToken"=>$this->txToken,
            "UUID"=>"",
            "rData"=>$rData,
            "urlKey"=>""
        );
        $data=json_encode($array,JSON_UNESCAPED_UNICODE);
        $mac=hash('sha256', "{$data}{$this->key}");
        $ksn="1";
        $str="data={$data}&mac={$mac}&ksn={$ksn}";
        $url=$this->url['register'];
        return redirect('http://icarry.me')->with();
        $form=<<<EOF
        <div style="display:flex;justify-content: center;align-items: center;height: 90vh;">
        <img src="https://icarry.me/images/loading.gif">
        </div>
        <form id="cardlink" method="post" target="_self" action="{$cardlink_api["register"]}">
        <input type="hidden" name="data" value='{$data}' />
        <input type="hidden" name="mac" value="{$mac}" />
        <input type="hidden" name="ksn" value="{$ksn}" />
        <input type="submit" style="display:none" value="卡號註冊" />
        </form>
        <script>document.getElementById("cardlink").submit();</script>
        EOF;
        return $form;
    }

    protected function cardlinkPost($url,$str) {
        return Curl::to($url)->withHeaders(['Content-Type:application/x-www-form-urlencoded','charset:utf-8'])
                    ->withTimeout(5)->withData( $str )->post();
    }

    protected function cardlinkPostJson($url,$str) {
        return Curl::to($url)->withHeaders(['Content-Type:application/json','charset:utf-8'])
                    ->withTimeout(5)->withData( $str )->post();
    }
}
