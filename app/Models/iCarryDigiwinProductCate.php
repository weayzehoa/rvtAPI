<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryDigiwinProductCate extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'digiwin_product_category';
    public $timestamps = FALSE;
}
