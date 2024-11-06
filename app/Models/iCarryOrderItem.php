<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryOrderItem extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'order_item';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'order_id',
        'vendor_id',
        'product_id',
        'product_model_id',
        'digiwin_no',
        'digiwin_payment_id',
        'price',
        'purchase_price',
        'gross_weight',
        'net_weight',
        'is_tax_free',
        'parcel_tax_code',
        'parcel_tax',
        'vendor_service_fee_percent',
        'shipping_verdor_percent',
        'product_service_fee_percent',
        'quantity',
        'return_quantity',
        'is_del',
        'admin_memo',
        'create_time',
        'promotion_ids',
        'product_name',
        'is_call',
        'direct_shipment',
        'shipping_memo',
        'not_purchase',
    ];
}
