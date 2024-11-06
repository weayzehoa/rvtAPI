<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryPayMethod as PayMethodDB;
use DB;

class PayMethodController extends Controller
{
    public function index()
    {
        $awsFileUrl = env('AWS_FILE_URL');
        $lang = request()->lang;
        $payMethods = PayMethodDB::where([['is_on',1],['type','!=','其它']])
            ->select([
                !empty($lang) && in_array($lang,['en','jp','kr','th']) ? "name_en as name" : 'name',
                'type',
                'value',
                'sort',
                DB::raw("(CASE WHEN image != '' THEN CONCAT('$awsFileUrl',image) END) as image"),
            ])->orderBy('sort','asc')->get();
        return $this->successResponse($payMethods->count(), $payMethods);
    }
}
