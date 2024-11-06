<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryAirportAddress as AirportAddressDB;
use Validator;

class AirportLocationController extends Controller
{
    protected $rules = [
        'to_country_id' => 'required|numeric',
        'lang' =>'nullable|string|in:en,jp,kr,th',
    ];

    public function index()
    {
        if (Validator::make(request()->all(), $this->rules)->fails()) {
            return $this->appCodeResponse('Error', 999, Validator::make(request()->all(), $this->rules)->errors(), 400);
        }
        foreach (request()->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $value;
            }
        }
        $airPorts = AirportAddressDB::where('country_id',$this->to_country_id)
            ->select([
                'value',
                !empty($this->lang) ? 'name_en as name' : 'name',
            ])->get();
        return $this->successResponse($airPorts->count(),$airPorts);
    }
}
