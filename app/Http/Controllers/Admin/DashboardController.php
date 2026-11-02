<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Hotel;
use App\Models\Partner;
use App\Models\PartnerAgreement;

class DashboardController extends Controller
{
    public function index()
    {
        $monthly = Booking::where('status', 'confirmed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('count(*) as orders, coalesce(sum(total_price), 0) as gross, coalesce(sum(platform_fee), 0) as fee')
            ->first();

        return view('admin.dashboard', [
            'pendingPartners' => Partner::where('verification_status', 'pending')->count(),
            'pendingHotels'   => Hotel::where('status', 'pending')->count(),
            'unsignedMou'     => PartnerAgreement::where('status', 'sent')->count(),
            'companyCount'    => Company::count(),
            'depositTotal'    => (int) Company::sum('deposit_balance'),
            'monthly'         => $monthly,
            'recent'          => Booking::with('partner')->latest()->limit(6)->get(),
        ]);
    }
}