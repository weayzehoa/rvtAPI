<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryLangJs as iCarryLangJsDB;
use App\Models\iCarryLangPack as iCarryLangPackDB;
use Validator;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->lang = request()->lang;
        $this->langs = ['','en','jp','kr','th'];
    }
    public function index()
    {
        if(in_array($this->lang,$this->langs)){
            empty($this->lang) ? $this->lang = 'tw' : '';
            $oldJSLangs = iCarryLangJsDB::orderBy('id','asc')->get();
            $i=1;
            $data = [];
            foreach ($oldJSLangs as $oldJSLang) {
                $data[$i] = $oldJSLang->{$this->lang};
                $i++;
            }
            $oldLangs = iCarryLangPackDB::orderBy('id','asc')->get();
            foreach ($oldLangs as $oldLang) {
                $data[$i] = $oldLang->{$this->lang};
                $i++;
            }
            $newLangs = [
                ['key_value' => 'finished', 'tw' => '已完成', 'en' => 'Finished', 'jp' => 'Finished', 'kr' => 'Finished', 'th' => 'Finished'],
                ['key_value' => 'wayForDelivery', 'tw' => '待出貨', 'en' => 'Wait for delivery', 'jp' => 'Wait for delivery', 'kr' => 'Wait for delivery', 'th' => 'Wait for delivery'],
                ['key_value' => 'handsFree', 'tw' => '免自提', 'en' => 'Hands-free', 'jp' => 'Hands-free', 'kr' => 'Hands-free', 'th' => 'Hands-free'],
                ['key_value' => 'tripleInvoice', 'tw' => '三聯式', 'en' => 'Triple invoice', 'jp' => 'Triple invoice', 'kr' => 'Triple invoice', 'th' => 'Triple invoice'],
                ['key_value' => 'doubleInvoice', 'tw' => '二聯式', 'en' => 'Double invoice', 'jp' => 'Double invoice', 'kr' => 'Double invoice', 'th' => 'Double invoice'],
                ['key_value' => 'receiptOfDonationCharityFoundation', 'tw' => '收據捐贈：慈善基金會', 'en' => 'Receipt of donation: Charity Foundation', 'jp' => 'Receipt of donation: Charity Foundation', 'kr' => 'Receipt of donation: Charity Foundation', 'th' => 'Receipt of donation: Charity Foundation'],
                ['key_value' => 'receiptOfDonationCharityFoundation', 'tw' => '收據捐贈：慈善基金會', 'en' => 'Receipt of donation: Charity Foundation', 'jp' => 'Receipt of donation: Charity Foundation', 'kr' => 'Receipt of donation: Charity Foundation', 'th' => 'Receipt of donation: Charity Foundation'],
                ['key_value' => 'barcodeCarrierForNaturalPersonCertificate', 'tw' => '自然人憑證條碼載具', 'en' => 'Barcode carrier for natural person certificate', 'jp' => 'Barcode carrier for natural person certificate', 'kr' => 'Barcode carrier for natural person certificate', 'th' => 'Barcode carrier for natural person certificate'],
                ['key_value' => 'ezPayCarrier', 'tw' => '智付寶載具', 'en' => 'ezPay carrier', 'jp' => 'ezPay carrier', 'kr' => 'ezPay carrier', 'th' => 'ezPay carrier'],
            ];
            for($i=0;$i<count($newLangs);$i++){
                $data[$newLangs[$i]['key_value']] = $newLangs[$i][$this->lang];
            }
            return response()->json($data,200);
        }else{
            return response()->json(null,200);
        }
    }
}
