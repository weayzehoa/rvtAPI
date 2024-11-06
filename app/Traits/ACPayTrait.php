<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\iCarryACPayPayment as ACPayPaymentDB;
use Curl;

trait ACPayTrait
{
    public function __construct()
    {
    }

    public function ACPay($out_trade_no, $total_fee, $email)
    {
        if(env('APP_ENV') == 'local'){
            $key = env("ACPAY_PAY_TEST_HASH_KEY");
            $merchant_no = env('ACPAY_PAY_TEST_MERCHANT_ID');
            $url = env('ACPAY_PAY_TEST_API_URL');
        }else{
            $key = env("ACPAY_PAY_HASH_KEY");
            $merchant_no = env('ACPAY_PAY_MERCHANT_ID');
            $url = env('ACPAY_PAY_API_URL');
        }
        $notify_url = env('ACPAY_PAY_NOTIFY_URL');
        $callback_url = $this->returnURL; //失敗後返回按鈕點了後到這裡
        $nonce_str = strtoupper(md5(time()));
        $fill_email = empty($email) ? "icarry4tw@gmail.com" : $email;
        $three_domain_secure = "Y";
        $service = 'vmj'; //信用卡交易
        $body = 'sale'; //銷貨訂單、商品描述。
        $str = "body={$body}&callback_url={$callback_url}&charset=UTF-8&fill_email={$fill_email}&merchant_no={$merchant_no}&nonce_str={$nonce_str}&notify_url={$notify_url}&out_trade_no={$out_trade_no}&service={$service}&sign_type=SHA-256&three_domain_secure={$three_domain_secure}&total_fee={$total_fee}&version=2.0";
        $sign = $this->acpay_makeSign($str, $key);

        $xmlData = "<xml><service>vmj</service>
        <version>2.0</version>
        <charset>UTF-8</charset>
        <sign_type>SHA-256</sign_type>
        <merchant_no>{$merchant_no}</merchant_no>
        <out_trade_no>{$out_trade_no}</out_trade_no>
        <body>sale</body>
        <total_fee>{$total_fee}</total_fee>
        <nonce_str>{$nonce_str}</nonce_str>
        <sign>{$sign}</sign>
        <notify_url>{$notify_url}</notify_url>
        <callback_url>{$callback_url}</callback_url>
        <fill_email>{$fill_email}</fill_email>
        <three_domain_secure>{$three_domain_secure}</three_domain_secure>
        </xml>";

        $headers = array(
            'Content-Type: application/xml'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = $this->acpay_xmlToJson($response);
        $data = json_decode($json, true);

        ACPayPaymentDB::create([
            'order_number' => $out_trade_no,
            'amount' => $total_fee,
            'post_json' =>$json,
        ]);
        return $data['code_url'];
    }

    private function acpay_xmlToJson($xml)
    {
        $json = json_encode(simplexml_load_string($this->acpay_excludeCData($xml)), JSON_PRETTY_PRINT);
        return $json;
    }

    private function acpay_excludeCData($xmlStr)
    {
        $xmlStr = str_replace('<![CDATA[', '', $xmlStr);
        $xmlStr = str_replace(']]>', '', $xmlStr);
        return $xmlStr;
    }

    private function acpay_makeSign($str, $key)
    {
        $full_str = "{$str}&key={$key}";
        $hash = hash('sha256', $full_str);
        return strtoupper($hash);
    }
}
