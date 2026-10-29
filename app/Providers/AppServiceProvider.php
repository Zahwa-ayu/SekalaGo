<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CurrencyService::class);
        $this->app->bind(
    \App\Services\Translation\TranslationProvider::class,
    \App\Services\Translation\MockTranslationProvider::class
    );
    $this->app->bind(
    \App\Services\Payment\PaymentGateway::class,
    \App\Services\Payment\MockPaymentGateway::class
    );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
