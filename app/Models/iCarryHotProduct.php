<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryHotProduct extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'hot_product';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    public $timestamps = false;
}
