<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorporateFinance
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user()->companyUser;

        abort_unless($member && $member->isFinance(), 403);

        return $next($request);
    }
}