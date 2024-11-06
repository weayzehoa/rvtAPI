<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UuidController extends Controller
{
    public function index()
    {
        $uuid = Str::uuid()->toString();
        return response()->json($uuid,200);
    }
}
