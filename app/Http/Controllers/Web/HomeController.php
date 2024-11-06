<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order as OrderDB;
use App\Models\User as UserDB;

class HomeController extends Controller
{
    /**
     * Show the welcome.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('web.welcome');
    }

    public function payTest()
    {
        $message = request()->message;
        $orderId = request()->order_id;
        if(!empty($orderId)){
            $order = OrderDB::findOrFail($orderId);
            return view('web.paytest',compact('order'));
        }
        if(!empty($message)){
            return view('web.paytest',compact('message'));
        }
        return view('web.paytest');
    }

    public function test()
    {
        return "測試用頁面";
    }
}
