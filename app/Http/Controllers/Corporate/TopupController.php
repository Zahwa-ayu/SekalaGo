<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\PlatformBankAccount;
use App\Models\TopupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TopupController extends Controller
{
    use CorporateHelpers;

    private const MAX_PENDING = 5;

    public function index(Request $request)
    {
        $company = $this->company($request);

        return view('corporate.topups.index', [
            'company'  => $company,
            'accounts' => PlatformBankAccount::active()->orderBy('sort_order')->orderBy('bank_name')->get(),
            'requests' => TopupRequest::with('account')
                ->where('company_id', $company->id)
                ->betweenDates('created_at', $request->query('from'), $request->query('to'))
                ->latest()
                ->paginate((int) config('sekalago.per_page', 15))
                ->withQueryString(),
            'minimum'  => (int) config('sekalago.topup_minimum', 100000),
        ]);
    }

    public function store(Request $request)
    {
        $company = $this->company($request);
        $minimum = (int) config('sekalago.topup_minimum', 100000);

        $data = $request->validate([
            'platform_bank_account_id' => ['required', 'integer', Rule::exists('platform_bank_accounts', 'id')->where('is_active', true)],
            'amount'                   => ['required', 'integer', 'min:' . $minimum, 'max:1000000000'],
            'transfer_date'            => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . today()->subDays(30)->toDateString()],
            'sender_name'              => ['nullable', 'string', 'max:100'],
            'note'                     => ['nullable', 'string', 'max:300'],
            'proof'                    => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        if (TopupRequest::where('company_id', $company->id)->where('status', 'pending')->count() >= self::MAX_PENDING) {
            throw ValidationException::withMessages(['amount' => __('ui.topup_too_many_pending', ['max' => self::MAX_PENDING])]);
        }

        $path = $request->file('proof')->store("topup-proofs/{$company->id}", 'local');

        try {
            TopupRequest::create([
                'code'                     => $this->newCode(),
                'company_id'               => $company->id,
                'requested_by'             => $request->user()->id,
                'platform_bank_account_id' => $data['platform_bank_account_id'],
                'amount'                   => $data['amount'],
                'transfer_date'            => $data['transfer_date'],
                'sender_name'              => $data['sender_name'] ?? null,
                'note'                     => $data['note'] ?? null,
                'proof_path'               => $path,
                'status'                   => 'pending',
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }

        return redirect()->route('corporate.topups.index')->with('success', __('ui.topup_submitted'));
    }

    /** Finance membatalkan permintaan yang masih menunggu. */
    public function cancel(Request $request, TopupRequest $topup)
    {
        $this->own($request, $topup);
        abort_unless($topup->status === 'pending', 404);

        $topup->update(['status' => 'cancelled', 'decided_by' => $request->user()->id, 'decided_at' => now()]);

        return back()->with('success', __('ui.topup_cancelled'));
    }

    public function proof(Request $request, TopupRequest $topup)
    {
        $this->own($request, $topup);
        abort_unless($topup->proof_path && Storage::disk('local')->exists($topup->proof_path), 404);

        return Storage::disk('local')->response($topup->proof_path);
    }

    private function own(Request $request, TopupRequest $topup): void
    {
        abort_unless($topup->company_id === $this->company($request)->id, 404);
    }

    private function newCode(): string
    {
        do {
            $code = 'TU-' . now()->format('ym') . '-' . strtoupper(Str::random(5));
        } while (TopupRequest::where('code', $code)->exists());

        return $code;
    }
}