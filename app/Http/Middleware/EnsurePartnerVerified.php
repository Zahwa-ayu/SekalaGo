<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $partner = $request->user()->partner;
        $message = $partner ? $partner->loginBlockedMessage() : __('ui.partner_login_pending');

        if ($message) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}