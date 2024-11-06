<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryUserAddress extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'user_address';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'name',
        'nation',
        'phone',
        'email',
        'address',
        'country',
        'city',
        'area',
        's_area',
        'zip_code',
        'id_card',
        'china_id_img1',
        'china_id_img2',
        'is_default',
    ];
}
