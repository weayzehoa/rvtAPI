<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryUserFavorite as UserFavoriteDB;
use App\Models\iCarryHotProduct as HotProductDB;
use DB;

class iCarryProduct extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $fillable = [
        'vendor_id',
        'category_id',
        'unit_name',
        'unit_name_id',
        'from_country_id',
        'product_sold_country',
        'name',
        'export_name_en',
        'brand',
        'serving_size',
        'shipping_methods',
        'price',
        'gross_weight',
        'net_weight',
        'title',
        'intro',
        'model_name',
        'model_type',
        'is_tax_free',
        'specification',
        'verification_reason',
        'status',
        'is_hot',
        'hotel_days',
        'airplane_days',
        'storage_life',
        'fake_price',
        'TMS_price',
        'allow_country',
        'allow_country_ids',
        'vendor_price',
        'unable_buy',
        'pause_reason',
        'tags',
        'is_del',
        'pass_time',
        'curation_text_top',
        'curation_text_bottom',
        'service_fee_percent',
        'package_data',
        'new_photo1',
        'new_photo2',
        'new_photo3',
        'new_photo4',
        'new_photo5',
        'type',
        'digiwin_product_category',
        'vendor_earliest_delivery_date',
        'shipping_fee_category_id', //棄用
        'ticket_price',
        'ticket_group',
        'ticket_memo',
        'direct_shipment',
        'eng_name',
    ];

    public function models(){
        $request = request();
        $models = $this->hasMany(ProductModelDB::class,'product_id','id')->where('is_del',0);
        if(!empty($request->zero_quantity) && $request->zero_quantity == 'yes'){
            $models = $models->where('quantity','<=',0);
        }
        if(!empty($request->low_quantity) && $request->low_quantity == 'yes'){
            $models = $models->whereRaw(" quantity < safe_quantity ");
        }
        if(!empty($request->zero_quantity) && $request->zero_quantity == 'yes'){
            $models = $models->where('quantity','<=',0);
        }
        return $models;
    }
    public function vendor(){
        return $this->belongsTo(VendorDB::class, 'vendor_id', 'id');
    }

    public function category(){
        return $this->belongsTo(CategoryDB::class, 'category_id', 'id');
    }

    /* 下面 function 前台使用 */
    public function userFavorites(){
        return $this->hasMany(UserFavoriteDB::class,'table_id','id');
    }

    public function images(){
        $host = env('AWS_FILE_URL');
        return $this->hasMany(ProductImageDB::class)->where('is_on',1)
        ->select([
            'product_id',
            DB::raw("CONCAT('$host',filename) as filename"),
            'sort',
        ])->orderBy('sort','asc');
    }

    public function image(){
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class)->where('is_on',1)
        ->select([
            'product_id',
            DB::raw("CONCAT('$host',filename) as filename"),
        ])->orderBy('sort','asc');
    }

    public function vendorLangs(){
        return $this->hasMany(VendorLangDB::class, 'vendor_id', 'vendor_id')
                ->select([
                    'vendor_id',
                    'lang',
                    'name',
                    'summary',
                    'description',
                ]);
    }

    public function langs(){
        return $this->hasMany(ProductLangDB::class,'product_id','id')
        ->select([
            'product_id',
            'lang',
            'name',
            'brand',
            'serving_size',
            'unable_buy',
            'title',
            'intro',
            'model_name',
            'specification',
            'curation_text_top',
            'curation_text_bottom',
        ]);
    }

    public function styles(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $styles = $this->hasMany(ProductModelDB::class,'product_id','id')
        ->select([
            'id as product_model_id',
            'product_id',
            'name',
            'sku',
            'quantity',
            'safe_quantity',
        ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $styles = $styles->addSelect([
                DB::raw("(CASE WHEN name_$lang != '' THEN name_$lang WHEN name_en != '' THEN name_en ELSE name END) as name"),
            ]);
        }
        return $styles;
    }

    public function packages(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $packages = $this->hasMany(ProductPackageDB::class,'product_id','id')
            ->join('product_model','product_model.id','product_packages.product_model_id')
            ->select([
                'product_packages.*',
                'product_model.name',
                'product_model.sku',
                'product_model.quantity',
                'product_model.safe_quantity',
            ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $packages = $packages->addSelect([
                DB::raw("(CASE WHEN product_model.name_{$lang} != '' THEN product_model.name_{$lang} WHEN product_model.name_en != '' THEN product_model.name_en ELSE product_model.name END) as name"),
            ]);
        }
        return $packages;
    }

    public function packs(){
        return $this->hasMany(ProductPackageDB::class,'product_id','id')
        ->join('product_model','product_model.id','product_packages.product_model_id')
        ->select([
            'product_packages.*',
            'product_model.name',
            'product_model.sku',
            'product_model.quantity',
            'product_model.safe_quantity',
        ]);
    }

    public function vendorHotProducts(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $fileHost = env('AWS_FILE_URL');
        $hotProducts = $this->hasMany(HotProductDB::class,'vendor_id','vendor_id')
            ->join('product','product.id','hot_product.product_id')
            ->join('vendor','vendor.id','hot_product.vendor_id')
            ->whereIn('product.status',[1,-3])
            ->select([
                'hot_product.vendor_id',
                'hot_product.product_id',
                'vendor.name as vendor_name',
                'product.name as product_name',
                'product.fake_price',
                'product.price',
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
            $hotProducts = $hotProducts->addSelect([
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) ELSE product.name END) as name"),
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$lang' limit 1) ELSE vendor.name END) as vendor_name"),
            ]);
        }
        $hotProducts = $hotProducts->orderBy('hot_product.hits','desc');
        return $hotProducts;
    }
}
