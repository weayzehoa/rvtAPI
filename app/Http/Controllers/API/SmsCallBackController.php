<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsLog as SmsLogDB;
use Twilio\TwiML\MessagingResponse as TwiML;

class SmsCallBackController extends Controller
{
    /*
        Mitake Response (三竹簡訊返回)
    */
    public function mitakeResponse()
    {
        $data = request()->all();
        $getResponse=json_encode($data);
        switch($data["StatusFlag"]){
            case '0':$status='預約傳送中';break;
            case '1':$status='已送達業者';break;
            case '2':$status='已送達業者';break;
            case '3':$status='已送達業者';break;
            case '4':$status='已送達手機';break;
            case '5':$status='內容有錯誤';break;
            case '6':$status='門號有錯誤';break;
            case '7':$status='簡訊已停用';break;
            case '8':$status='逾時無送達';break;
            case '9':$status='預約已取消';break;
            default:$status='無法辨識的錯誤';break;
        }
        SmsLogDB::where('msg_id',$data["msgid"])->update([
            'status' => $status,
            'get_response' => $getResponse,
        ]);
    }
    /*
        twilioCallback (Twilio 簡訊返回)
    */
    public function twilioCallback(Request $request)
    {
        $data = $request->all();
        $getResponse=json_encode($data);
        $smsLog = SmsLogDB::where('msg_id',$data['MessageSid'])->first();
        $smsLog = $smsLog->update([
            'get_response' => $getResponse,
            'status' => $data['MessageStatus'],
        ]);
        return response()->noContent()->header('Content-Type', 'text/xml');
    }
}
