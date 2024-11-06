<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryCategoryLang as CategoryLangDB;

class iCarryCategory extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'category';
    public $timestamps = FALSE;
    protected $fillable = [
        'id',
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'intro',
        'logo',
        'cover',
        'sort_id',
        'is_on',
    ];
    public function products(){
        $this->request = request();
        $this->langs = ['en','jp','kr','th'];
        $this->rules = [
            'type' => 'nullable|string|in:vendor,product',
            'lang' =>'nullable|string|in:en,jp,kr,th',
        ];
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $value;
            }
        }
        $products = $this->hasMany(ProductDB::class,'category_id','id')
        ->where('product.is_del',0)
        ->whereIn('product.status',[1,-3]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->select([
                    'product.id',
                    'product.category_id',
                    DB::raw("(CASE WHEN product_langs.lang = '$this->lang' and product_langs.name != '' THEN product_langs.name WHEN product_langs.lang = 'en' and product_langs.name !='' THEN product_langs.name ELSE product.name END) as name"),
                ]);
        }else{
            $products = $products->select([
                'product.id',
                'product.category_id',
                'product.name',
            ]);
        }
        return $products;
    }
    public function langs(){
        return $this->hasMany(CategoryLangDB::class,'category_id','id');
    }
}
