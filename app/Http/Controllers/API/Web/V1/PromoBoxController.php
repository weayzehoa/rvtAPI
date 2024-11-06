<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryPromoBox as PromoBoxDB;
use Validator;
use DB;

class PromoBoxController extends Controller
{
    public function __construct()
    {
        //將request()放入變數中
        $this->request = request();

        //定義語言及檔案路徑變數
        $this->langs = ['en','jp','kr','th'];

        $this->rules = [
            'lang'  => 'nullable|in:en,jp,kr,th|string|max:5',
        ];
    }

    public function index()
    {
        if (Validator::make($this->request->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->rules)->errors(), 400);
        }

        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $$key = $data[$key] = $value;
            }
        }

        $promoBoxes = PromoBoxDB::where('is_on',1)->where(function($query){
            $query = $query->where(function($q1){
                $q1 = $q1->where('start_time','<=',date('Y-m-d H:i:s'))->orWhereNull('start_time');
            });
        })->where(function($q2){
            $q2 = $q2->where('end_time','>=',date('Y-m-d H:i:s'))->orWhereNull('end_time');
        });
        $promoBoxes = $promoBoxes->select([
            'id',
            'title',
            'text_teaser as teaser',
            'text_complete as content',
            'img_url as image',
        ]);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $promoBoxes = $promoBoxes->addSelect([
                DB::raw("(CASE WHEN title_{$this->lang} != '' THEN title_{$this->lang} ELSE (CASE WHEN title_en != '' THEN title_en ELSE title END) END) as title"),
                DB::raw("(CASE WHEN text_teaser_{$this->lang} != '' THEN text_teaser_{$this->lang} ELSE (CASE WHEN text_teaser_en != '' THEN text_teaser_en ELSE text_teaser END) END) as teaser"),
                DB::raw("(CASE WHEN text_complete_{$this->lang} != '' THEN text_complete_{$this->lang} ELSE (CASE WHEN text_complete_en != '' THEN text_complete_en ELSE text_complete END) END) as content"),
            ]);
        }
        $promoBoxes = $promoBoxes->orderBy('id','desc')->get();
        return $this->successResponse($promoBoxes->count(), $promoBoxes);
    }
}
