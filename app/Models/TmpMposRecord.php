<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TmpMposRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'machine_list_id',
        'order_number',
        'order_time',
        'shipping_method',
        'shipping_time',
        'pay_method',
        'pay_time',
        'skey',
        'amount',
        'boxes',
        'nation',
        'mobile',
        'birthday',
        'response',
        'status',
        'refund_amount',
        'shipping_number',
        'device_order_number',
        'is_close',
        'close_response',
        'close_time',
        'free_shipping',
        'base_shipping_fee',
        'each_box_shipping_fee',
        'payment_percent',
        'refund_response',
        'cancel_response',
        'is_print',
        'book_shipping_date',
    ];
}