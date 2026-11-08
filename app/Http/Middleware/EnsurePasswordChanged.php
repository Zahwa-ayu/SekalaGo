<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Selama password sementara belum diganti, hanya halaman ganti password, logout, dan pilihan bahasa yang boleh dibuka
        if ($user && $user->must_change_password
            && ! $request->routeIs('account.password.*', 'logout', 'preferences.*')) {

            // Ingat halaman yang dituju supaya pengguna kembali ke sana setelah mengganti password
            if ($request->isMethod('GET') && ! $request->expectsJson()) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('account.password.edit');
        }

        return $next($request);
    }
}