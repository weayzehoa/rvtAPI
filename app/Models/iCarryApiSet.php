<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryApiSet extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'api_set';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
}
