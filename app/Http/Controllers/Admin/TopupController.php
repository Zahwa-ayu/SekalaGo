<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TopupRequest;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TopupController extends Controller
{
    public function __construct(private DepositService $deposits)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, ['pending', 'approved', 'rejected', 'cancelled', 'all'], true), 404);

        $requests = TopupRequest::with('company', 'account', 'requester', 'decider')
            ->betweenDates('created_at', $request->query('from'), $request->query('to'))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'pending' DESC")
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('admin.topups.index', [
            'requests' => $requests,
            'status'   => $status,
            'counts'   => TopupRequest::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    /** Setujui setelah mencocokkan dengan mutasi rekening bank. Saldo bertambah sebesar jumlah yang diterima. */
    public function approve(Request $request, TopupRequest $topup)
    {
        $data = $request->validate([
            'approved_amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'decision_note'   => ['nullable', 'string', 'max:500'],
        ]);

        if ((int) $data['approved_amount'] !== (int) $topup->amount && blank($data['decision_note'] ?? null)) {
            throw ValidationException::withMessages(['decision_note' => __('ui.topup_note_required_diff')]);
        }

        DB::transaction(function () use ($topup, $data, $request) {
            $locked = TopupRequest::lockForUpdate()->findOrFail($topup->id);

            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['topup' => __('ui.topup_not_pending')]);
            }

            $this->deposits->credit(
                Company::findOrFail($locked->company_id),
                (int) $data['approved_amount'],
                'topup',
                'Top-up ' . $locked->code,
                $locked->code,
                $request->user()
            );

            $locked->update([
                'status'          => 'approved',
                'approved_amount' => $data['approved_amount'],
                'decided_by'      => $request->user()->id,
                'decided_at'      => now(),
                'decision_note'   => $data['decision_note'] ?? null,
            ]);
        });

        return back()->with('success', __('ui.topup_approved_done'));
    }

    public function reject(Request $request, TopupRequest $topup)
    {
        $data = $request->validate(['decision_note' => ['required', 'string', 'max:500']]);

        DB::transaction(function () use ($topup, $data, $request) {
            $locked = TopupRequest::lockForUpdate()->findOrFail($topup->id);

            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['topup' => __('ui.topup_not_pending')]);
            }

            $locked->update([
                'status'        => 'rejected',
                'decided_by'    => $request->user()->id,
                'decided_at'    => now(),
                'decision_note' => $data['decision_note'],
            ]);
        });

        return back()->with('success', __('ui.topup_rejected_done'));
    }

    public function proof(TopupRequest $topup)
    {
        abort_unless($topup->proof_path && Storage::disk('local')->exists($topup->proof_path), 404);

        return Storage::disk('local')->response($topup->proof_path);
    }
}