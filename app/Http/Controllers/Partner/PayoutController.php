<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayoutController extends Controller
{
    use PartnerHelpers;

    public function index(Request $request, PayoutService $payouts)
    {
        $partner = $this->partner($request);

        $data = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'cancelled'])],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $history = Payout::where('partner_id', $partner->id)
            ->search($request->query('q'), ['code', 'transfer_reference', 'bank_name', 'bank_account_name', 'bank_account_no', 'partner.company_name'])
            ->when($data['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) >= ?', [$date]))
            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) <= ?', [$date]))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('partner.payouts.index', [
            'partner' => $partner,
            'summary' => $payouts->overview($partner->id)->first(),
            'history' => $history,
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