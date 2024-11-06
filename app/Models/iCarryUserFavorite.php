<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryProduct as ProductDB;
// use App\Models\iCarryProductImage as ProductImageDB;
use DB;

class iCarryUserFavorite extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'user_favorite';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'table_id',
        'table_name',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class,'user_id','id');
    }

    public function products(){
        return $this->hasMany(ProductDB::class,'product_id','id');
    }

    // public function image()
    // {
    //     $host = env('AWS_FILE_URL');
    //     return $this->hasOne(ProductImageDB::class,'product_id','product_id')
    //             ->where('is_on',1)->orderBy('sort','asc')->select([
    //                 'product_id',
    //                 DB::raw("CONCAT('$host',filename) as filename"),
    //             ]);
    // }
}
