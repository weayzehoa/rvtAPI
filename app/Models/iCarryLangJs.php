<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryLangJs extends Model
{
    use HasFactory;
    protected $connection = 'icarryLang';
    protected $table = 'language_js';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = null;
    const UPDATED_AT = 'update_time';
}
