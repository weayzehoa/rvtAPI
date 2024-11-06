<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationLang as CurationLangDB;
use DB;
use Validator;

class CurationController extends Controller
{
    public function __construct()
    {
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
            request()->request->add(['userId' => $this->userId]);
        }
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $this->cate = request()->cate;
        $this->rules = [
            'cate' => 'required|in:home,category',
            'lang' => 'nullable|in:en,jp,kr,th',
        ];
        $this->langRules = [
            'lang' => 'nullable|in:en,jp,kr,th',
        ];
    }

    public function index()
    {
        //驗證失敗返回訊息
        if (Validator::make(request()->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->rules)->errors(), 400);
        }
        $now = date('Y-m-d H:i:s');
        //首頁策展 home, 分類策展 category
        $curations = CurationDB::where([['is_on',1],['category',$this->cate]])->where(function ($query) use ($now) {
                        $query->where([['start_time','<=',$now],['end_time','>=',$now]])
                            ->orWhere([['start_time','<=',$now],['end_time',null]])
                            ->orWhere([['start_time',null],['end_time',null]])
                            ->orWhere([['start_time',null],['end_time','>=',$now]]);
                    });

        //語言資料
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $curations = $curations->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.sub_title END) as sub_title"),
                DB::raw("(CASE WHEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.caption END) as caption"),
            ]);
        }
        //共有幾筆
        $totalCount = $curations->count();
        //找出最終資料
        $curations = $curations->orderBy('sort','asc')->get();
        //資料整理
        $i = 0;
        foreach ($curations as $curation) {
            $curation->background_image ? $curation->background_image = $this->awsFileUrl.$curation->background_image : '';
            //header只能有一個, 清除type讓前端找不到
            if($curation->type == 'header'){
                if($i>0){
                    unset($curation->type);
                }
                $i++;
            }

            if($curation->type == 'image'){ //圖片版型
                $curation->images = $curation->images;
            }
            if($curation->type == 'event'){ //活動版型
                $curation->events = $curation->events;
            }
            if($curation->type == 'block'){ //宮格版型
                $curation->blocks = $curation->blocks;
            }
            if($curation->type == 'nowordblock'){ //宮格(無字)版型
                $curation->noWordBlocks = $curation->noWordBlocks;
            }
            if($curation->type == 'vendor'){ //品牌版型
                $curation->vendors = $curation->vendors;
            }
            if($curation->type == 'product'){ //產品版型
                $curation->products = $curation->products;
                foreach ($curation->products as $product) {
                    unset($product->langs); //清除後台用的語言資料
                }
            }
        }
        return $this->successResponse($curations->count(), $curations);
    }

    public function show($id)
    {
        //驗證失敗返回訊息
        if (Validator::make(request()->all(), $this->langRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->langRules)->errors(), 400);
        }

        $now = date('Y-m-d H:i:s');
        $curation = CurationDB::where('is_on',1)->where(function ($query) use ($now) {
            $query->where([['start_time','<=',$now],['end_time','>=',$now]])
                ->orWhere([['start_time','<=',$now],['end_time',null]])
                ->orWhere([['start_time',null],['end_time',null]])
                ->orWhere([['start_time',null],['end_time','>=',$now]]);
        });
        $curation = $curation->select(['*']);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $curation = $curation->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.sub_title END) as sub_title"),
                DB::raw("(CASE WHEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT caption from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.caption END) as caption"),

                // 'main_title_'.$this->lang => CurationLangDB::whereColumn('curations.id', 'curation_langs.curation_id')
                //                     ->where('lang',$this->lang)->select('main_title')->limit(1),
                // 'sub_title_'.$this->lang => CurationLangDB::whereColumn('curations.id', 'curation_langs.curation_id')
                //                     ->where('lang',$this->lang)->select('sub_title')->limit(1),
                // 'caption_'.$this->lang => CurationLangDB::whereColumn('curations.id', 'curation_langs.curation_id')
                //                     ->where('lang',$this->lang)->select('caption')->limit(1),
            ]);
        }
        $curation = $curation->findOrFail($id);
        $curation->background_image ? $curation->background_image = $this->awsFileUrl.$curation->background_image : '';

        if($curation->type == 'image'){ //圖片版型
            $curation->images = $curation->images;
        }
        if($curation->type == 'event'){ //活動版型
            $curation->events = $curation->events;
        }
        if($curation->type == 'block'){ //宮格版型
            $curation->blocks = $curation->blocks;
        }
        if($curation->type == 'nowordblock'){ //宮格版型(無字)
            $curation->noWordBlocks = $curation->noWordBlocks;
        }
        if($curation->type == 'vendor'){ //品牌版型
            $curation->vendors = $curation->vendors;
        }
        if($curation->type == 'product'){ //產品版型
            $curation->products = $curation->products;
        }
        return $this->dataResponse($curation,'curations',$id);
    }
}
