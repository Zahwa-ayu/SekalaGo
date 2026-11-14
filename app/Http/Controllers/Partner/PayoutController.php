<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Storage;
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

    /** Mitra hanya bisa membuka bukti transfer pencairannya sendiri. */
    public function proof(Request $request, Payout $payout)
    {
        abort_unless($payout->partner_id === $this->partner($request)->id, 404);
        abort_unless($payout->proof_path && Storage::disk('local')->exists($payout->proof_path), 404);

        return Storage::disk('local')->response($payout->proof_path);
    }
}