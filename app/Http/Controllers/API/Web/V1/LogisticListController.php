<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryAirportAddress as AirportAddressDB;

class LogisticListController extends Controller
{
    public function __construct()
    {
        //將request()放入變數中
        $this->request = request();

        //定義語言及檔案路徑變數
        $this->langs = ['en','jp','kr','th'];

        $this->rules = [
            'lang'  => 'nullable|in:en,jp,kr,th|string|max:5',
        ];
    }

    public function index()
    {
        if (Validator::make($this->request->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make($this->request->all(), $this->rules)->errors(), 400);
        }

        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $$key = $value;
            }
        }

        $shippings = ShippingFeeDB::where('is_on',1);
        if (!empty($this->lang) && in_array($this->lang, $this->langs)) {
            $shippings = $shippings->select([
                'to_country_id' => CountryDB::whereColumn('countries.name','shipping_set.shipping_methods')->select('id')->limit(1),
                'shipping_methods_en as logistic_type',
                'shipping_methods_en as ship_to',
                'product_sold_country as ship_from',
                'description_en as description',
            ]);
        }else{
            $shippings = $shippings->select([
                'to_country_id' => CountryDB::whereColumn('countries.name','shipping_set.shipping_methods')->select('id')->limit(1),
                'shipping_methods as logistic_type',
                'shipping_methods as ship_to',
                'product_sold_country as ship_from',
                'description_tw as description',
            ]);
        }
        $count = $shippings->count();
        $shippings = $shippings->orderBy('to_country_id','desc')->get()->groupBy('ship_from')->all();

        $i=0;
        foreach ($shippings as $from => $shipping) {
            $data[$i]['ship_from'] = $from;
            $j = 0;
            foreach($shipping as $s){
                if((empty($s->to_country_id) && strstr($s->ship_to,'當地')) || (empty($s->to_country_id) && strstr($s->ship_to,'Local'))){
                    $tmp = CountryDB::where('name',$from)->first();
                    !empty($tmp) ? $s->to_country_id = $tmp->id : '';
                }
                if($s->ship_to == '當地機場' || $s->ship_to == 'Local Airport'){
                    !empty($this->lang) && in_array($this->lang, $this->langs) ? $s->airport_pickup_location = AirportAddressDB::where('country_id',$s->to_country_id)->select(['id','name_en as name','pickup_time_start','pickup_time_end'])->get() : $s->airport_pickup_location = AirportAddressDB::where('country_id',$s->to_country_id)->select(['id','name','pickup_time_start','pickup_time_end'])->get();
                }
                unset($s->ship_from);
                $data[$i]['ship_to_list'][$j] = $s->toArray();
                $j++;
            }
            rsort($data[$i]['ship_to_list']);
            $i++;
        }
        return $this->successResponse($count, $data);
    }
}
