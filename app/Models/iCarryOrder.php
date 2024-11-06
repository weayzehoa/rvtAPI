<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryOrderItem as OrderItemDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\GateOrderShipping as OrderShippingDB;
use DB;

class iCarryOrder extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'orders';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'order_number',
        'user_id',
        'origin_country',
        'ship_to',
        'from',
        'to',
        'book_shipping_date',
        'receiver_name',
        'receiver_id_card',
        'receiver_nation_number',
        'receiver_phone_number',
        'receiver_tel',
        'receiver_email',
        'receiver_address',
        'receiver_birthday',
        'receiver_province',
        'receiver_city',
        'receiver_area',
        'receiver_zip_code',
        'receiver_keyword',
        'receiver_key_time',
        'shipping_method',
        'invoice_time',
        'invoice_type',
        'invoice_sub_type',
        'invoice_number',
        'invoice_title',
        'invoice_address',
        'spend_point',
        'amount',
        'shipping_fee',
        'parcel_tax',
        'pay_method',
        'get_point',
        'exchange_rate',
        'shipping_number',
        'shipping_memo',
        'promotion_code',
        'discount',
        'admin_memo',
        'user_memo',
        'vendor_memo',
        'partner_order_number',
        'partner_country',
        'pay_time',
        'buyer_name',
        'buyer_email',
        'buyer_id_card',
        'carrier_type',
        'carrier_num',
        'love_code',
        'print_flag',
        'shipping_time',
        'buy_memo',
        'billOfLoading_memo',
        'special_memo',
        'new_shipping_memo',
        'star_color',
        'tax_refund',
        'domain',
        'create_type',
        'create_id',
        'is_invoice_no',
        'is_invoice_cancel',
        'invoice_memo',
        'china_id_img1',
        'china_id_img2',
        'is_del',
        'is_call',
        'is_print',
        'is_invoice',
        'status',
    ];

    // //前台訂單資料用
    public function orderItems()
    {
        $lang = request()->lang;
        $langs = ['en','jp','kr','th'];
        $orders = $this->hasMany(OrderItemDB::class,'order_id','id')
        ->join('product_model','product_model.id','order_item.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->join('vendor','vendor.id','order_item.vendor_id');
        $awsFileUrl = env('AWS_FILE_URL');
        $orders = $orders->select([
            'order_item.order_id',
            'vendor.name as vendor_name',
            'order_item.product_name',
        ]);

        if(!empty($lang) && in_array($lang,$langs)){
            $orders = $orders->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$lang' limit 1) is not null THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendor.id and vendor_langs.lang = '$lang' limit 1) ELSE vendor.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) is not null THEN (SELECT name from product_langs where product_langs.product_id = product.id and product_langs.lang = '$lang' limit 1) ELSE order_item.product_name END) as product_name"),
            ]);
        }
        $fileHost = env('AWS_FILE_URL');
        $orders = $orders->addSelect([
                'order_item.quantity',
                'product.fake_price',
                'order_item.price',
                'order_item.gross_weight',
                DB::raw('order_item.quantity * order_item.price as amount_price'),
                // 'image' => ProductImageDB::whereColumn('product_images.product_id','order_item.product_id')->where('product_images.is_on',1)->where('product_images.is_on',1)->orderBy('sort','asc')->select([DB::raw("CONCAT('$awsFileUrl',filename)")])->limit(1),
                'product.airplane_days',
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

        return $orders;
    }

    //前台使用者訂單用
    public function itemsImage()
    {
        $awsFileUrl = env('AWS_FILE_URL');
        $orders = $this->hasMany(OrderItemDB::class,'order_id','id');
        $orders = $orders->join('product_model','product_model.id','order_item.product_model_id')
            ->join('product','product.id','product_model.product_id');
        $orders = $orders->select([
            'order_item.order_id',
            DB::raw("(CASE
                WHEN product.new_photo1 is not null THEN CONCAT('$awsFileUrl',product.new_photo1)
                WHEN product.new_photo2 is not null THEN CONCAT('$awsFileUrl',product.new_photo2)
                WHEN product.new_photo3 is not null THEN CONCAT('$awsFileUrl',product.new_photo3)
                WHEN product.new_photo4 is not null THEN CONCAT('$awsFileUrl',product.new_photo4)
                WHEN product.new_photo5 is not null THEN CONCAT('$awsFileUrl',product.new_photo5)
                WHEN product.photo1 is not null THEN product.photo1
                WHEN product.photo2 is not null THEN product.photo2
                WHEN product.photo3 is not null THEN product.photo3
                WHEN product.photo4 is not null THEN product.photo4
                ELSE null END) as image"),
            // 'image' => ProductImageDB::whereColumn('product_images.product_id','order_item.product_id')
            // ->where('product_images.is_on',1)->orderBy('sort','asc')->select([DB::raw("CONCAT('$awsFileUrl',filename)")])->limit(1),
        ]);
        return $orders;
    }

    public function shippingInfo(){
        return $this->hasOne(ShippingFeeDB::class,'to','to','from');
    }

    public function orderShippings(){
        return $this->hasMany(OrderShippingDB::class,'order_id','id')->select(['id','order_id','express_way','express_no']);
    }
}
