<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCountry as CountryDB;

class CountryController extends Controller
{
    public function index()
    {
        $lang = request()->lang;
        $countries = CountryDB::orderBy('sort','asc')
            ->select([
                'id',
                in_array($lang,['en','jp','kr','th']) ? "name_$lang as name" : 'name',
                'lang',
                'code',
                'sort',
            ])->get();
        return $this->successResponse($countries->count(), $countries);
    }
}
