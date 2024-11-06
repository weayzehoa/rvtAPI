<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use DB;
use Validator;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->request = request();
        if(auth('webapi')->check()){
            $this->userId = auth('webapi')->user()->id;
            request()->request->add(['userId' => $this->userId]);
        }
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->showRules = [
            'lang' => 'nullable|in:en,jp,kr,th',
        ];
    }
    public function show($id)
    {
        //驗證失敗返回訊息
        if (Validator::make($this->request->all(), $this->showRules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->showRules)->errors(), 400);
        }
        request()->request->add(['vendor_id' => $id]); //將id放入request()
        foreach ($this->request->all() as $key => $value) {
            $this->{$key} = $$key = $value;
        }
        $vendor = VendorDB::with('curations','productsData')->where('is_on',1)
            ->select([
                'id',
                'name',
                DB::raw("(CASE WHEN img_logo is not null THEN CONCAT('$this->awsFileUrl',img_logo) END) as img_logo"),
                DB::raw("(CASE WHEN img_cover is not null THEN CONCAT('$this->awsFileUrl',img_cover) END) as img_cover"),
                DB::raw("(CASE WHEN img_site is not null THEN CONCAT('$this->awsFileUrl',img_site) END) as img_site"),
                'description',
            ]);
        if(isset($this->lang) && in_array($this->lang,$this->langs)){
            $vendor = $vendor->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$this->lang' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$this->lang' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendor.name END) as name"),
                DB::raw("(CASE WHEN (SELECT description from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$this->lang' limit 1) != '' THEN (SELECT description from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$this->lang' limit 1) WHEN (SELECT description from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT description from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendor.description END) as description"),
            ]);
        }
        $vendor = $vendor->findOrFail($id);

        if(count($vendor->curations) > 0){
            foreach ($vendor->curations as $curation) {
                if(count($curation->products) > 0){
                    foreach ($curation->products as $product) {
                        unset($product->langs);
                    }
                }
            }
        }
        if(count($vendor->productsData) > 0){
            foreach ($vendor->productsData as $product) {
                $this->userId = 6604;
                if(!empty($this->userId)){
                    $product->is_favorite = 0;
                    if(count($product->userFavorites) > 0){
                        foreach($product->userFavorites as $user){
                             if($user->user_id == $this->userId){
                                if($product->id == $user->table_id){
                                    $product->is_favorite = 1;
                                    break;
                                }
                             }
                        }
                    }
                }
                $temp = [];
                if($product->model_type != 1){
                    unset($product->product_model_id);
                    unset($product->sku);
                    $product->outOffStock = 0;
                }
                $product->model_type == 2 ? $product->styles = $temp = $product->styles : '';
                $product->model_type == 3 ? $product->packs = $temp = $product->packs : '';
                if(count($temp) > 0){
                    foreach ($temp as $tmp) {
                        $tmp->quantity <= 0 ? $product->outOffStock++ : '';
                    }
                    $product->outOffStock == count($temp) ? $product->outOffStock = 1 : $product->outOffStock = 0;
                }
                $product->status == -3 ? $product->outOffStock = 1 : '';
                //清除不需要的變數
                unset($product->vendor_id);
                unset($product->status);
                unset($product->model_type);
                unset($product->packs);
                unset($product->styles);
                unset($product->userFavorites);
            }
        }
        return $this->successResponse(['curations' => $vendor->curations->count(),'products' => $vendor->productsData->count()], $vendor);
    }
}
