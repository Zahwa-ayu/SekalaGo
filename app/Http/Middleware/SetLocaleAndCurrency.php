<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAndCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Prioritas: akun (jika login) -> session -> default
        $locale   = $user?->preferred_locale ?? session('locale', config('sekalago.default_locale'));
        $currency = $user?->preferred_currency ?? session('currency', config('sekalago.default_currency'));

        if (! array_key_exists($locale, config('sekalago.locales'))) {
            $locale = config('sekalago.default_locale');
        }

        app()->setLocale($locale);
        app(CurrencyService::class)->use($currency);

        return $next($request);
    }
}