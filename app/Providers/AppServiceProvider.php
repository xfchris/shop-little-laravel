<?php

namespace App\Providers;

use App\Lib\GstPlaceToPay;
use Dnetix\Redirection\PlacetoPay;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Configuracion de placetopay
        $this->app->singleton(GstPlaceToPay::class, function ($app) {
            return new GstPlaceToPay(
                new PlacetoPay([
                    'login' => env('PLACE_TO_PAY_LOGIN'),
                    'tranKey' => env('PLACE_TO_PAY_TRANKEY'),
                    'url' => env('PLACE_TO_PAY_URL'),
                    'rest' => [
                        'timeout' => 45, // (optional) 15 by default
                        'connect_timeout' => 30, // (optional) 5 by default
                    ]
                ])
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
