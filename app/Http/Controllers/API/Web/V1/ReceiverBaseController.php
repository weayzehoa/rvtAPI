<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryReceiverBaseSetting as ReceiverBaseSettingDB;
use Carbon\Carbon;
use Auth;
use DB;
use Validator;

class ReceiverBaseController extends Controller
{
    protected $rules = [
        'month' => 'nullable|numeric|min:1|max:12',
        'year' =>'nullable|numeric|min:2021|max:2032',
    ];

    public function index()
    {
        $month = date('n');
        $year = date('Y');
        $data = [];
        if (Validator::make(request()->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->rules)->errors(), 400);
        }
        foreach (request()->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $$key = $value;
            }
        }
        //月份小於等於9補0
        $month <= 9 ? '0'.$month : '';
        //本月第一天是星期幾
        $thisMonthfirstDayWeek = date('w',strtotime($year.'-'.$month.'-01'));
        //往回推到星期天
        $firstSunday = Carbon::create($year, $month, 1, 0)->subDays($thisMonthfirstDayWeek);
        //往回推後的日期
        $firstDay = substr($firstSunday,0,10);
        //五周最後一天
        $lastDay = substr($firstSunday->addDays(34),0,10);
        //最後一天若還是在目前月份，再加一周
        if(substr($lastDay,5,2) == $month){
            $lastDay = substr($firstSunday->addDays(7),0,10);
        }
        // 找出資料
        $receiverBases = ReceiverBaseSettingDB::whereBetween('select_date', array($firstDay, $lastDay))
            ->select([
                'select_date as date',
                'week',
                'type',
                'is_ok',
                'memo',
            ])
            ->orderBy('select_date','asc')->orderBy('type','asc')->get()->groupBy('date')->all();
        if(count($receiverBases) > 0){
            $i=0;
            foreach ($receiverBases as $date => $rBases) {
                $data[$i]['date'] = $date;
                foreach ($rBases as $rBase) {
                    if($rBase->type == 'pickup'){
                        $data[$i]['week'] = $rBase->week;
                        $data[$i][$rBase->type] = $rBase->is_ok;
                        $data[$i][$rBase->type.'_memo'] = $rBase->memo;
                    }
                }
                $i++;
            }
        }
        $total = count($data);

        //資料依照select_date日期群組(因為有每天有四筆)後，拆分5周
        // $data = collect($data)->groupBy('date')->chunk(7)->all();
        return $this->successResponse($total, $data);
    }
}
