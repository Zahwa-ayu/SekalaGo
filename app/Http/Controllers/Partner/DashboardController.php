<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use PartnerHelpers;

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $hotelStatus = $partner->hotels()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $vehicleStatus = $partner->vehicles()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthly = Booking::where('partner_id', $partner->id)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('count(*) as orders, coalesce(sum(total_price), 0) as gross, coalesce(sum(platform_fee), 0) as fee, coalesce(sum(vendor_revenue), 0) as net')
            ->first();

        $upcoming = Booking::where('partner_id', $partner->id)
            ->where('status', 'confirmed')
            ->where('check_in', '>=', today())
            ->orderBy('check_in')
            ->limit(6)
            ->get();

        return view('partner.dashboard', compact('partner', 'hotelStatus', 'vehicleStatus', 'monthly', 'upcoming'));
    }
}