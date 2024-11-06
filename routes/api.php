<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function(){
    if(env('APP_ENV') == 'production'){
        return redirect()->to('https://icarry.me');
    }else{
        return redirect()->to('docs');
    }
});

//前台API群組
use App\Http\Controllers\API\Web\V1\CurationController as CurationV1;
use App\Http\Controllers\API\Web\V1\ProductController as ProductV1;
use App\Http\Controllers\API\Web\V1\VendorController as VendorV1;
use App\Http\Controllers\API\Web\V1\CountryController as CountryV1;
use App\Http\Controllers\API\Web\V1\CategoryController as CategoryV1;
use App\Http\Controllers\API\Web\V1\ShippingMethodController as ShippingMethodV1;
use App\Http\Controllers\API\Web\V1\ReceiverBaseController as ReceiverBaseV1;
use App\Http\Controllers\API\Web\V1\UserLoginController as UserLoginV1;
use App\Http\Controllers\API\Web\V1\UserController as UserV1;
use App\Http\Controllers\API\Web\V1\UserFavoriteController as UserFavoriteV1;
use App\Http\Controllers\API\Web\V1\UserAddressController as UserAddressV1;
use App\Http\Controllers\API\Web\V1\ShoppingCartController as ShoppingCartV1;
use App\Http\Controllers\API\Web\V1\OrderController as OrderV1;
use App\Http\Controllers\API\Web\V1\EsunCardlinkController as EsunCardlinkV1;
use App\Http\Controllers\API\Web\V1\PayMethodController as PayMethodV1;
use App\Http\Controllers\API\Web\V1\AirportLocationController as AirportLocationV1;
use App\Http\Controllers\API\Web\V1\PromoBoxController as PromoBoxV1;
use App\Http\Controllers\API\Web\V1\LogisticListController as LogisticListV1;

Route::prefix('web')->name('webapi.')->group(function() {
    //第一版
    Route::prefix('v1')->name('v1.')->group(function() {
        Route::post('login', [UserLoginV1::class , 'login']);
        Route::post('logout', [UserLoginV1::class , 'logout']);
        Route::post('refresh', [UserLoginV1::class , 'refresh']);
        Route::post('register', [UserLoginV1::class , 'register']);
        Route::post('sendVerifyCode', [UserLoginV1::class , 'sendVerifyCode']);
        Route::post('confirmVerifyCode', [UserLoginV1::class , 'confirmVerifyCode']);
        Route::post('forgetPassword', [UserLoginV1::class , 'forgetPassword']);

        if(env('APP_ENV') == 'local'){ //開發測試用. 正式機關閉
            Route::post('me', [UserLoginV1::class , 'me']);
        }

        Route::resource('curation', CurationV1::class, ['only' => ['index', 'show']]);

        Route::get('product/availableDate/{id}', [ProductV1::class, 'availableDate'])->name('product.availableDate'); //預計最快提貨日
        Route::get('product/allowCountry/{id}', [ProductV1::class, 'allowCountry'])->name('product.allowCountry'); //允許寄送國家
        Route::resource('product', ProductV1::class, ['only' => ['index', 'show']]);
        Route::resource('airportLocation', AirportLocationV1::class, ['only' => ['index']]);

        Route::resource('vendor', VendorV1::class, ['only' => ['index', 'show']]);
        Route::resource('country', CountryV1::class, ['only' => ['index']]);
        Route::resource('payMethod', PayMethodV1::class, ['only' => ['index']]);
        Route::resource('category', CategoryV1::class, ['only' => ['index']]);
        Route::resource('shippingMethod', ShippingMethodV1::class, ['only' => ['index']]);
        Route::resource('receiverBase', ReceiverBaseV1::class, ['only' => ['index']]);
        Route::resource('user', UserV1::class, ['only' => ['show','update']]);
        Route::resource('userFavorite', UserFavoriteV1::class, ['only' => ['store','destroy']]);
        Route::resource('userAddress', UserAddressV1::class);

        Route::get('shoppingCart/total',[ShoppingCartV1::class , 'total'])->name('shoppingCart.total'); //購物車數量
        Route::get('shoppingCart/amount',[ShoppingCartV1::class , 'amount'])->name('shoppingCart.amount'); //金額計算
        Route::get('shoppingCart/checkPromoCode',[ShoppingCartV1::class , 'checkPromoCode'])->name('shoppingCart.checkPromoCode'); //檢查促銷代碼
        Route::resource('shoppingCart', ShoppingCartV1::class);

        Route::get('order/buyAgain/{id}', [OrderV1::class, 'buyAgain'])->name('order.buyAgain'); //再買一次
        Route::resource('order', OrderV1::class);

        Route::resource('promoBox', PromoBoxV1::class, ['only' => ['index']]);

        Route::get('esunCardlink/register', [EsunCardlinkV1::class, 'register'])->name('esunCardlink.register');
        Route::get('esunCardlink/cancel', [EsunCardlinkV1::class, 'cancel'])->name('esunCardlink.cancel');

        Route::resource('logisticList', LogisticListV1::class, ['only' => ['index']]);
    });
});

//後台API群組
Route::prefix('admin')->name('adminapi.')->group(function() {
    //第一版
    Route::prefix('v1')->name('v1.')->group(function() {
    });
});

//商家後台API群組
Route::prefix('vendor')->name('vendorapi.')->group(function() {
    //第一版
    Route::prefix('v1')->name('v1.')->group(function() {
    });
});

//一般共用API
use App\Http\Controllers\API\UuidController as UUID;
use App\Http\Controllers\API\LanguageController as Language;
Route::resource('uuid', UUID::class, ['only' => ['index']]);
Route::resource('language', Language::class, ['only' => ['index']]);

//金流返回
use App\Http\Controllers\API\PayCallBackController as PayCallBack;
Route::post('acpay/notify', [PayCallBack::class, 'acpayNotify'])->name('api.acpay.notify');
Route::post('newebpay/return', [PayCallBack::class, 'newebpayReturn'])->name('api.newebpay.return');
Route::post('newebpay/notify', [PayCallBack::class, 'newebpayNotify'])->name('api.newebpay.notify');
Route::post('newebpay/getCode', [PayCallBack::class, 'newebpayGetCode'])->name('api.newebpay.getCode');
Route::post('esun/alipayNotify', [PayCallBack::class, 'esunAlipayNotify'])->name('api.esun.notify');
Route::get('tashin/postBack', [PayCallBack::class, 'tashinPostBack'])->name('api.tashin.postBack'); //銀聯使用get返回
Route::post('tashin/resultBack', [PayCallBack::class, 'tashinResultBack'])->name('api.tashin.resultBack');
//簡訊返回
use App\Http\Controllers\API\SmsCallBackController as SmsCallBack;
Route::get('mitakeResponse', [SmsCallBack::class, 'mitakeResponse'])->name('api.mitakeResponse');
Route::post('twilioCallback', [SmsCallBack::class, 'twilioCallback'])->name('api.twilioCallback');

//測試環境用
use App\Http\Controllers\API\PayTestController as PayTest; //金流測試用
use App\Http\Controllers\API\TestController as Test; //其他測試用
if(env('APP_ENV') != 'production'){
    Route::get('newebpayCancel', [PayTest::class,'newebpayCancel'])->name('newebpayCancel');
    Route::resource('payTest', PayTest::class);
    Route::resource('test', Test::class);
}

//檢查IP,放在下面
// Route::middleware(['checkIp'])->group(function () {

// });
