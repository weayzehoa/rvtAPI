<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//前台 web 用的路由 網址看起來就像 https://localhost/{名稱}
use App\Http\Controllers\Web\HomeController;
Route::get('/', [HomeController::class, 'index'])->name('index');

//金流測試
Route::get('/payTest', [HomeController::class, 'payTest'])->name('payTest');

//金流 form post 轉換用
use App\Http\Controllers\PayController;
Route::resource('pay', PayController::class,['only' => ['index']]);
