<?php

if (! function_exists('format_price')) {
    function format_price(int|float $idrAmount, ?string $currency = null): string
    {
        return app(\App\Services\CurrencyService::class)->format($idrAmount, $currency);
    }
}