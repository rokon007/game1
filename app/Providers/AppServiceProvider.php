<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if(config('app.env') !== 'local') {
        // \URL::forceScheme('https');
        // }
        Paginator::useBootstrap();

        // ইউজারের টাইমজোন খুঁজে বের করা
        $timezone = Session::get('user_timezone') ??
                    (Auth::check() ? Auth::user()->timezone : null) ??
                    config('app.timezone');

        // গ্লোবাল টাইমজোন সেট করা Carbon-এর জন্য
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
        Carbon::setLocale(config('app.locale')); // লোকেল চাইলে সেট করুন

    }
}
