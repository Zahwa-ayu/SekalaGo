<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    use PartnerHelpers;

    public function index(Request $request, PayoutService $payouts)
    {
        $partner = $this->partner($request);

        return view('partner.payouts.index', [
            'partner' => $partner,
            'summary' => $payouts->overview($partner->id)->first(),
            'history' => Payout::where('partner_id', $partner->id)->latest()->paginate(15),
        ]);
    }
}