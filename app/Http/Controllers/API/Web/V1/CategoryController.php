<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use DB;
use Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->request = request();
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->rules = [
            'type' => 'nullable|string|in:vendor,product',
            'lang' =>'nullable|string|in:en,jp,kr,th',
        ];
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $value;
            }
        }
    }

    public function index()
    {
        if (Validator::make($this->request->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->rules)->errors(), 400);
        }
        $categories = CategoryDB::where('is_on',1);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $categories =$categories->join('category_langs','category_langs.category_id','category.id')
                ->select([
                    'category.id',
                    DB::raw("(CASE WHEN category_langs.lang = 'en' and category_langs.name != '' THEN category_langs.name ELSE category.name END) as name"),
                    DB::raw("(CASE WHEN category_langs.lang = 'en' and category_langs.intro != '' THEN category_langs.intro ELSE category.intro END) as intro"),
                ]);
        }else{
            $categories =$categories->select([
                'category.id',
                'category.name',
                'category.intro',
            ]);
        }
        $categories =$categories->addSelect([
            DB::raw("(CASE WHEN logo is not null THEN CONCAT('$this->awsFileUrl',logo) ELSE null END) as logo"),
            DB::raw("(CASE WHEN cover is not null THEN CONCAT('$this->awsFileUrl',cover) ELSE null END) as cover"),
            'sort_id',
        ]);

        if(!empty($this->type) && $this->type == 'product'){
            $categories = $categories->with('products');
        }

        $categories =$categories->orderBy('sort_id','asc')->get();

        if(!empty($this->type) && $this->type == 'vendor'){

            foreach ($categories as $category) {
                $vendors = VendorDB::where('is_on',1)->whereRaw("FIND_IN_SET('$category->id',vendor.categories)");
                if(!empty($this->lang) && in_array($this->lang,$this->langs)){
                    $vendors = $vendors->join('vendor_langs','vendor_langs.vendor_id','vendor.id')
                        ->select([
                            'vendor.id',
                            DB::raw("(CASE WHEN vendor_langs.lang = '$this->lang' and vendor_langs.name != '' THEN vendor_langs.name WHEN vendor_langs.lang = 'en' and vendor_langs.name !='' THEN vendor_langs.name ELSE vendor.name END) as name"),
                        ]);
                }else{
                    $vendors = $vendors->orderBy('id','asc')->select(['id','name']);
                }
                $category->vendors = $vendors->get();
            }
        }
        return $this->successResponse($categories->count(), $categories);
    }
}
