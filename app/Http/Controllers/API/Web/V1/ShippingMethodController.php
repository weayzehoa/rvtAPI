<?php

namespace App\Http\Controllers\API\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryShippingMethod as ShippingMethodDB;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethodDB::all();
        return $this->successResponse($shippingMethods->count(), $shippingMethods);
    }
}
