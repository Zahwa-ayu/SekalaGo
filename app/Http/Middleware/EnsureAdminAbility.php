<?php

namespace App\Http\Middleware;

use App\Support\AdminAbilities;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAbility
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = $request->route()?->getName();

        if ($user && $name && ! AdminAbilities::allowsRoute($user, $name)) {
            abort(403, __('ui.admin_forbidden'));
        }

        return $next($request);
    }
}