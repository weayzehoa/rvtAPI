<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //註冊自定義帳號資料表的密碼檢驗規則
        \Auth::provider('self-eloquent', function ($app, $config) {
            return New \App\Libs\SelfEloquentProvider($app['hash'], $config['model']);
        });

        //使用 Bootstrap 分頁樣式
        Paginator::useBootstrap();

        //只要有作紀錄動作，將IP寫入特定的紀錄IP欄位
        Activity::saving(function(Activity $activity) { $activity->ip = $activity->ip = request()->ip();});
    }
}
