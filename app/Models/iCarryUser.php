<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject; //JWT用, 加入 JWTSubject implements 及 官方提供的兩個 function
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\iCarryUserFavorite as UserFavoriteDB;
use App\Models\iCarryUserAddress as UserAddressDB;
use App\Models\GateSmsLog as SmsLogDB;
use App\Models\iCarryShoppingCart as ShoppingCartDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use DB;

use App\Traits\LanguagePack;

class iCarryUser extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use LanguagePack;
    protected $connection = 'icarry';
    protected $table = 'users';
    //變更 Laravel 預設 create_time 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'nation',
        'mobile',
        'email',
        'pwd',
        'password',
        'refer_id',
        'refer_code',
        'name',
        'status',
        'verify_code',
        'from_site',
        'from_token',
        'points',
        'smsTime',
        'address',
        'id_card',
        'asiamiles_account',
        'asiamiles_name',
        'asiamiles_last_name',
        'avatar',
        'ip',
        'mark',
        'is_mark',
        'carrier_type',
        'carrier_num',
        'remember_me',
        'memo',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    /*
    * 覆蓋Laravel中預設的getAuthPassword方法, 返回使用者的password和salt欄位
    * @return array
    */
    public function getAuthPassword()
    {
        return ['password' => $this->attributes['password'], 'salt' => ''];
    }

    // Rest omitted for brevity
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    //後台使用
    public function pointLogs(){
        return $this->hasMany(UserPointDB::class,'user_id','id');
    }

    public function smsLogs(){
        return $this->hasMany(SmsLogDB::class,'user_id','id')->orderBy('create_time','desc');
    }

    public function address(){
        return $this->hasMany(UserAddressDB::class,'user_id','id');
    }

    public function shoppingCarts(){
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $shoppingCartTable = env('DB_ICARRY').'.'.(new ShoppingCartDB)->getTable();

        return $this->hasMany(ShoppingCartDB::class,'user_id','id')
        ->join($productModelTable,$productModelTable.'.id',$shoppingCartTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->select([
            $shoppingCartTable.'.*',
            $vendorTable.'.name as vendor_name',
            DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
            $productTable.'.unit_name',
            $productTable.'.gross_weight',
            $productTable.'.price',
            $productModelTable.'.digiwin_no',
        ]);

    }

    //以下前台使用
    public function favoriteProducts(){
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $products = $this->hasMany(UserFavoriteDB::class,'user_id','id')
            ->join('product','product.id','user_favorite.table_id')
            ->join('vendor','vendor.id','product.vendor_id')
            ->where('user_favorite.table_name','product')
            ->where('vendor.is_on',1)
            ->where('product.is_del',0)
            ->whereIn('product.status',[1,-3])
            ->select([
                'user_favorite.user_id',
                'product.id as product_id',
                'vendor.id as vendor_id',
                'product.name',
                'vendor.name as vendor_name',
                'product.fake_price',
                'product.price',
            DB::raw("(CASE
                WHEN product.new_photo1 is not null THEN CONCAT('$this->awsFileUrl',product.new_photo1)
                WHEN product.new_photo2 is not null THEN CONCAT('$this->awsFileUrl',product.new_photo2)
                WHEN product.new_photo3 is not null THEN CONCAT('$this->awsFileUrl',product.new_photo3)
                WHEN product.new_photo4 is not null THEN CONCAT('$this->awsFileUrl',product.new_photo4)
                WHEN product.new_photo5 is not null THEN CONCAT('$this->awsFileUrl',product.new_photo5)
                WHEN product.photo1 is not null THEN product.photo1
                WHEN product.photo2 is not null THEN product.photo2
                WHEN product.photo3 is not null THEN product.photo3
                WHEN product.photo4 is not null THEN product.photo4
                ELSE null END) as image"),
                // 'image' => ProductImageDB::whereColumn('products.id', 'product_images.product_id')->where('product_images.is_on',1)
                // ->select(DB::raw("(CASE WHEN filename is not null THEN (CONCAT('$this->awsFileUrl',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendor.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = 'en' limit 1) ELSE product.name END) as name"),
            ]);
        }
        return $products;
    }

    public function favoriteVendors(){
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $vendors = $this->hasMany(UserFavoriteDB::class,'user_id','id')
            ->join('vendor','vendor.id','user_favorite.table_id')
            ->where('user_favorite.table_name','vendor')
            ->where('vendors.is_on',1)
            ->select([
                'user_favorite.user_id',
                'vendor.id as vendor_id',
                'vendor.name',
                DB::raw("(CASE WHEN vendor.img_logo is not null THEN CONCAT('$this->awsFileUrl',vendor.img_logo) END) as img_logo"),
                DB::raw("(CASE WHEN vendor.img_cover is not null THEN CONCAT('$this->awsFileUrl',vendor.img_cover) END) as img_cover"),
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.name END) as name"),
            ]);
        }
        return $vendors;
    }

    public function userAddress(){
        $aesKey = env('APP_AESENCRYPT_KEY');
        return $this->hasMany(UserAddressDB::class,'user_id','id')
        ->select([
            'id',
            'user_id',
            'name',
            'nation',
            // 'phone',
            DB::raw("IF(phone IS NULL,'',AES_DECRYPT(phone,'$aesKey')) as phone"),
            'country',
            'area',
            's_area',
            'city',
            'address',
            'china_id_img1', //資料內含完整網址
            'china_id_img2', //資料內含完整網址
            'is_default',
        ])->orderBy('country','asc')->orderBy('is_default','desc')->orderBy('id','desc');
    }

    public function userOrders(){
        $lang = request()->lang;
        $langs = ['en','jp','kr','th'];
        $this->translate = $this->translate(['免自提','超商代碼','ATM轉帳','尚未付款','信用卡','待出貨','集貨中','已出貨','已完成','已取消']);
        //字串取代
        $row = 'pay_method'; //欄位名稱
        $replaceByLike = ''; //使用like方式
        $replaceByReplace = 'pay_method'; //使用replace方式
        $findStr = ['智付通','國際','玉山','台新','資策會','CVS','ATM','信用卡']; //找出字串
        $replaceStr = ['','','','',$this->translate['免自提'],$this->translate['超商代碼'],$this->translate['ATM轉帳'],$this->translate['信用卡']]; //要取代的字串
        for($i=0;$i<count($findStr);$i++){
            $replaceByLike .= " WHEN $row like '%".$findStr[$i]."%' THEN REPLACE($row,'".$findStr[$i]."','".$replaceStr[$i]."') ";
            $replaceByReplace = "REPLACE(".$replaceByReplace.",'".$findStr[$i]."','".$replaceStr[$i]."')";
        }
        $row = 'status';
        $replaceStatus = '';
        $status = [0 => $this->translate['尚未付款'], 1 => $this->translate['待出貨'], 2 => $this->translate['集貨中'], 3 => $this->translate['已出貨'], 4 => $this->translate['已完成'], -1 => $this->translate['已取消']];
        foreach ($status as $key => $value) {
            $replaceStatus .= " WHEN $row = $key THEN '$value' ";
        }
        $orders = $this->hasMany(OrderDB::class,'user_id','id')->with('itemsImage');
        // $orders = $this->hasMany(OrderDB::class,'user_id','id');
        $orders = $orders->where('create_time','>=','2020-01-01 00:00:00.000'); //限制訂單2020-01-01之後
        $orders = $orders->select([
                'id',
                'user_id',
                'order_number',
                DB::raw("(CASE WHEN NOW() >= DATE_ADD(create_time, INTERVAL 6 HOUR) THEN 0
                ELSE 1 END) as in_six_hour"),
                DB::raw("(DATE_FORMAT(create_time,'%Y-%m-%d')) as create_date"),
                DB::raw("(amount - spend_point - discount + shipping_fee + parcel_tax) as price"),
                DB::raw("($replaceByReplace) as pay_method"),
                DB::raw("(CASE $replaceStatus END) as order_status"),
                'status',
                'to_country_id' => CountryDB::whereColumn('orders.ship_to', 'countries.name')->select('id')->limit(1),
                'ship_to',
                'receiver_name',
                'receiver_address',
                DB::raw("(SELECT count(id) from order_item where orders.id = order_item.order_id) as totalItems ")
            ])->orderBy('create_time','desc')->limit(100);

        if(!empty($lang) && in_array($lang,$langs)){
            $orders = $orders->addSelect([
                DB::raw("(CASE WHEN (SELECT name_$lang from countries where countries.name = orders.ship_to limit 1) is not null THEN (SELECT name_$lang from countries where countries.name = orders.ship_to limit 1) ELSE (CASE WHEN (SELECT name_en from countries where countries.name = orders.ship_to limit 1) is not null THEN (SELECT name_en from countries where countries.name = orders.ship_to limit 1) ELSE (SELECT name from countries where countries.name = orders.ship_to limit 1) END) END) as ship_to"),
            ]);
        }

        return $orders;
    }

    public function pointsHistory(){
        return $this->hasMany(UserPointDB::class,'user_id','id')
            ->where('is_dead',0)
            ->select([
                'user_id',
                'points',
                'point_type',
                DB::raw("(DATE_FORMAT(create_time,'%Y-%m-%d')) as create_time"),
            ])->orderBy('create_time','desc');
    }

}
