<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMouSigned
{
    public function handle(Request $request, Closure $next): Response
    {
        $partner = $request->user()->partner;

        if (! $partner || ! $partner->hasSignedAgreement()) {
            return redirect()->route('partner.agreement.show')->with('error', __('ui.mou_required'));
        }

        return $next($request);
    }
}