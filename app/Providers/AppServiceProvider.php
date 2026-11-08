<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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
        // Batas login: 5 percobaan per menit untuk kombinasi email dan IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('email')) . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => __('ui.too_many_login', ['seconds' => $headers['Retry-After'] ?? 60])]);
                });
        });

        // Email reset password dalam bahasa aplikasi
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->subject(__('ui.reset_mail_subject'))
                ->line(__('ui.reset_mail_line'))
                ->action(__('ui.reset_mail_action'), $url)
                ->line(__('ui.reset_mail_expire', ['count' => $minutes]))
                ->line(__('ui.reset_mail_ignore'));
        });
    }
}
