<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryVendorShop as VendorShopDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryVendorLang as VendorLangDB;

use App\Models\iCarryProductImage as ProductImageDB;

use App\Models\OrderShipping as OrderVendorShippingDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryHotProduct as HotProductDB;
use App\Models\iCarryUserFavorite as UserFavoriteProductDB;
use DB;

class iCarryVendor extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function products(){
        return $this->hasMany(ProductDB::class,'vendor_id','id')->where('is_del',0)->orderBy('id','desc');
    }

    protected $fillable = [
        'name','company','VAT_number','boss','contact_person',
        'tel','fax','email','categories','address','shipping_setup',
        'shipping_verdor_percent','is_on','summary','description',
        'shopping_notice','service_fee',
        'cover','img_cover','img_logo','img_site','shipping_self',
        'factory_address','product_sold_country','curation','notify_email','bill_email',
        'pause_start_date', 'pause_end_date','use_sf'
    ];

    public function shops(){
        return $this->hasMany(VendorShopDB::class,'vendor_id','id');
    }

    public function accounts(){
        return $this->hasMany(VendorAccountDB::class,'vendor_id','id');
    }

    //前台API用
    public function curations(){
        $this->langs = ['en','jp','kr','th'];
        $this->lang = request()->lang;
        $now = date('Y-m-d H:i:s');
        $curations = $this->hasMany(CurationDB::class,'vendor_id','id')->with('products')
            ->where([['is_on',1],['category','vendor']])->where(function ($query) use ($now) {
                $query->where([['start_time','<=',$now],['end_time','>=',$now]])
                    ->orWhere([['start_time','<=',$now],['end_time',null]])
                    ->orWhere([['start_time',null],['end_time',null]])
                    ->orWhere([['start_time',null],['end_time','>=',$now]]);
            })->select([
                'id',
                'vendor_id',
                'main_title',
                'show_main_title',
                'sub_title',
                'show_sub_title',
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $curations = $curations->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.sub_title END) as sub_title"),
            ]);
        }
        return $curations;
    }
    //前台API用
    public function productsData(){
        $fileHost = env('AWS_FILE_URL');
        $langs = ['en','jp','kr','th'];
        foreach (request()->all() as $key => $value) {
            $this->{$key} = $$key = $value;
        }
        $products = $this->hasMany(ProductDB::class,'vendor_id','id')
        ->join('product_model','product_model.product_id','product.id')
        ->where('product.is_del',0)
        ->whereIn('product.status',[1,-3])
        ->select([
            'product.id',
            'product.vendor_id',
            'product.name',
            'product.fake_price',
            'product.price',
            'product.pass_time',
            'product.model_type',
            'product.status',
            'product.curation_text_top',
            'product.curation_text_bottom',
            'hotest' => HotProductDB::whereColumn('hot_product.product_id','product.id')->select([
                DB::raw("(CASE WHEN hot_product.vendor_id = 482 THEN FLOOR( 444 + RAND() * 2345) ELSE hot_product.hits END) as hotest")
            ])->limit(1),
            DB::raw('(CASE WHEN product.model_type = 1 and product_model.quantity <= 0 THEN 1 ELSE 0 END) as outOffStock'),
            DB::raw("(CASE
            WHEN product.new_photo1 is not null THEN CONCAT('$fileHost',product.new_photo1)
            WHEN product.new_photo2 is not null THEN CONCAT('$fileHost',product.new_photo2)
            WHEN product.new_photo3 is not null THEN CONCAT('$fileHost',product.new_photo3)
            WHEN product.new_photo4 is not null THEN CONCAT('$fileHost',product.new_photo4)
            WHEN product.new_photo5 is not null THEN CONCAT('$fileHost',product.new_photo5)
            WHEN product.photo1 is not null THEN product.photo1
            WHEN product.photo2 is not null THEN product.photo2
            WHEN product.photo3 is not null THEN product.photo3
            WHEN product.photo4 is not null THEN product.photo4
            ELSE null END) as image"),
        ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.curation_text_top END) as curation_text_top"),
                DB::raw("(CASE WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.curation_text_bottom END) as curation_text_bottom"),
            ]);
        }
        $products = $products->orderBy('hotest','desc');

        return $products;
    }

}
