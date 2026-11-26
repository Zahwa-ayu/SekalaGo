<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerOffers
{
    /** Rute yang hanya berlaku untuk satu jenis usaha. */
    private const HOTEL = ['partner.hotels.*', 'partner.room-types.*', 'partner.stays.*'];
    private const CAR   = ['partner.vehicles.*', 'partner.blackouts.*', 'partner.rentals.*'];

    public function handle(Request $request, Closure $next): Response
    {
        $partner = $request->user()?->partner;
        $name    = $request->route()?->getName();

        if ($partner && $name) {
            $hotelOnly = Str::is(self::HOTEL, $name) && ! $partner->offersHotels();
            $carOnly   = Str::is(self::CAR, $name) && ! $partner->offersCars();

            abort_if($hotelOnly || $carOnly, 403, __('ui.partner_type_forbidden'));
        }

        return $next($request);
    }
}