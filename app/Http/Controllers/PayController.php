<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayController extends Controller
{
    //金流 form post 轉換用
    public function index()
    {
        if($pay = request()->pay){
            return view('pay', compact('pay'));
        }else{
            return view('pay');
        }
    }
}
