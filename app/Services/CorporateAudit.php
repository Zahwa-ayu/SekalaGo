<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\Company;
use App\Models\DepositTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CorporateAudit
{
    /** Mutasi saldo satu periode: saldo awal, pergerakan per jenis, dan saldo akhir. */
    public function statement(Company $company, Carbon $from, Carbon $to): array
    {
        $opening = (int) (DepositTransaction::where('company_id', $company->id)
            ->where('created_at', '<', $from)
            ->orderByDesc('id')
            ->value('balance_after') ?? 0);

        $rows = DepositTransaction::where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('creator')
            ->orderBy('id')
            ->get();

        return [
            'opening'          => $opening,
            'rows'             => $rows,
            'topups'           => (int) $rows->where('type', 'topup')->sum('amount'),
            'charges'          => (int) $rows->where('type', 'charge')->sum('amount'),   // negatif
            'refunds'          => (int) $rows->where('type', 'refund')->sum('amount'),
            'other'            => (int) $rows->whereNotIn('type', ['topup', 'charge', 'refund'])->sum('amount'),
            'closing'          => $opening + (int) $rows->sum('amount'),
            'closing_recorded' => $rows->isNotEmpty() ? (int) $rows->last()->balance_after : $opening,
        ];
    }

    /**
     * Sepuluh pemeriksaan atas seluruh riwayat perusahaan.
     *
     * @return Collection<int, array{key: string, status: string, items: Collection}>
     */
    public function checks(Company $company): Collection
    {
        $ledger = DepositTransaction::where('company_id', $company->id)->orderBy('id')->get();
        $txById = $ledger->keyBy('id');

        $deposit    = Booking::where('company_id', $company->id)->where('payment_method', 'deposit')->get();
        $extensions = BookingExtension::whereIn('booking_id', $deposit->pluck('id'))
            ->where('payment_method', 'deposit')
            ->get()
            ->groupBy('booking_id');

        // Pemilik tiap pemotongan: pesanan atau perpanjangan yang merujuk ke baris buku besar itu
        $owners = [];

        foreach ($deposit as $booking) {
            $extensionSum = (int) $extensions->get($booking->id, collect())->sum('amount');

            if ($id = $this->txId($booking->payment_reference, 'DEP-')) {
                $owners[$id][] = ['label' => $booking->booking_code, 'expected' => (int) $booking->total_price - $extensionSum];
            }

            foreach ($extensions->get($booking->id, collect()) as $extension) {
                if ($id = $this->txId($extension->payment_reference, 'DEP-')) {
                    $owners[$id][] = [
                        'label'    => $booking->booking_code . ' (' . __('ui.audit_extension') . ')',
                        'expected' => (int) $extension->amount,
                    ];
                }
            }
        }

        return collect([
            $this->make('balance_matches', $this->balanceMatches($company, $ledger)),
            $this->make('chain_intact', $this->chainIntact($ledger)),
            $this->make('no_negative', $this->noNegative($ledger)),
            $this->make('charges_matched', $this->chargesMatched($ledger, $owners)),
            $this->make('bookings_charged', $this->bookingsCharged($deposit, $txById)),
            $this->make('refunds_matched', $this->refundsMatched($ledger, $deposit, $txById)),
            $this->make('topups_documented', $this->topupsDocumented($ledger), 'warn'),
            $this->make('approvals_recorded', $this->approvalsRecorded($company)),
            $this->make('exempt_over_limit', $this->exemptOverLimit($company), 'warn'),
            $this->make('pending_exposure', $this->pendingExposure($company), 'info'),
        ]);
    }

    /** clean, attention, atau issues. */
    public function verdict(Collection $checks): string
    {
        return match (true) {
            $checks->contains('status', 'fail') => 'issues',
            $checks->contains('status', 'warn') => 'attention',
            default                             => 'clean',
        };
    }

    // ------------------------------------------------------------ pemeriksaan

    private function balanceMatches(Company $company, Collection $ledger): Collection
    {
        $sum     = (int) $ledger->sum('amount');
        $balance = (int) $company->deposit_balance;

        return $sum === $balance ? collect() : collect([[
            'label'  => __('ui.audit_balance_diff'),
            'detail' => __('ui.audit_balance_diff_detail', [
                'balance' => $this->idr($balance), 'sum' => $this->idr($sum), 'diff' => $this->idr($balance - $sum),
            ]),
        ]]);
    }

    private function chainIntact(Collection $ledger): Collection
    {
        $items   = collect();
        $running = 0;

        foreach ($ledger as $tx) {
            $running += (int) $tx->amount;

            if ($running !== (int) $tx->balance_after) {
                $items->push([
                    'label'  => $this->txLabel($tx),
                    'detail' => __('ui.audit_chain_broken', ['expected' => $this->idr($running), 'recorded' => $this->idr($tx->balance_after)]),
                ]);

                $running = (int) $tx->balance_after;   // lanjutkan dari angka tercatat supaya tiap putusnya hanya dilaporkan sekali
            }
        }

        return $items;
    }

    private function noNegative(Collection $ledger): Collection
    {
        return $ledger->filter(fn ($tx) => (int) $tx->balance_after < 0)->map(fn ($tx) => [
            'label'  => $this->txLabel($tx),
            'detail' => __('ui.audit_negative_balance', ['balance' => $this->idr($tx->balance_after)]),
        ])->values();
    }

    private function chargesMatched(Collection $ledger, array $owners): Collection
    {
        $items = collect();

        foreach ($ledger->where('type', 'charge') as $tx) {
            $mine = $owners[$tx->id] ?? [];

            if (count($mine) === 0) {
                $items->push(['label' => $this->txLabel($tx), 'detail' => __('ui.audit_charge_orphan', ['amount' => $this->idr(abs($tx->amount))])]);
            } elseif (count($mine) > 1) {
                $items->push(['label' => $this->txLabel($tx), 'detail' => __('ui.audit_charge_shared', ['codes' => collect($mine)->pluck('label')->implode(', ')])]);
            } elseif (abs((int) $tx->amount) !== $mine[0]['expected']) {
                $items->push(['label' => $this->txLabel($tx), 'detail' => __('ui.audit_charge_amount', [
                    'code' => $mine[0]['label'], 'charged' => $this->idr(abs($tx->amount)), 'expected' => $this->idr($mine[0]['expected']),
                ])]);
            }
        }

        return $items;
    }

    private function bookingsCharged(Collection $deposit, Collection $txById): Collection
    {
        $items = collect();

        foreach ($deposit->whereIn('payment_status', ['paid', 'refunded']) as $booking) {
            $id = $this->txId($booking->payment_reference, 'DEP-');
            $tx = $id ? $txById->get($id) : null;

            if (! $tx || $tx->type !== 'charge') {
                $items->push(['label' => $booking->booking_code, 'detail' => __('ui.audit_booking_no_charge', ['amount' => $this->idr($booking->total_price)])]);
            }
        }

        return $items;
    }

    private function refundsMatched(Collection $ledger, Collection $deposit, Collection $txById): Collection
    {
        $items  = collect();
        $linked = [];

        foreach ($deposit->where('payment_status', 'refunded') as $booking) {
            $id = $this->txId($booking->refund_reference, 'DEP-RF-');
            $tx = $id ? $txById->get($id) : null;

            if ($id) {
                $linked[$id] = true;
            }

            if (! $tx || $tx->type !== 'refund' || (int) $tx->amount !== (int) $booking->refund_amount) {
                $items->push(['label' => $booking->booking_code, 'detail' => __('ui.audit_refund_mismatch', ['amount' => $this->idr($booking->refund_amount)])]);
            }
        }

        foreach ($ledger->where('type', 'refund') as $tx) {
            if (! isset($linked[$tx->id])) {
                $items->push(['label' => $this->txLabel($tx), 'detail' => __('ui.audit_refund_orphan', ['amount' => $this->idr($tx->amount)])]);
            }
        }

        return $items;
    }

    private function topupsDocumented(Collection $ledger): Collection
    {
        return $ledger->where('type', 'topup')->filter(fn ($tx) => blank($tx->reference))->map(fn ($tx) => [
            'label'  => $this->txLabel($tx),
            'detail' => __('ui.audit_topup_no_reference', ['amount' => $this->idr($tx->amount)]),
        ])->values();
    }

    private function approvalsRecorded(Company $company): Collection
    {
        return Booking::where('company_id', $company->id)
            ->whereNotNull('approval_reason')
            ->whereIn('status', ['confirmed', 'rejected'])
            ->where(fn ($q) => $q->whereNull('decided_by')->orWhereNull('decided_at'))
            ->get()
            ->map(fn ($booking) => [
                'label'  => $booking->booking_code,
                'detail' => __('ui.audit_approval_missing', ['status' => __('ui.booking_status_' . $booking->status)]),
            ]);
    }

    /** Pesanan terkonfirmasi di atas limit yang tidak melewati persetujuan (Finance bebas persetujuan). */
    private function exemptOverLimit(Company $company): Collection
    {
        return Booking::where('company_id', $company->id)
            ->where('status', 'confirmed')
            ->whereNotNull('policy_limit')
            ->whereColumn('total_price', '>', 'policy_limit')
            ->whereNull('approval_reason')
            ->with('user')
            ->get()
            ->map(fn ($booking) => [
                'label'  => $booking->booking_code,
                'detail' => __('ui.audit_over_limit', [
                    'who' => $booking->user?->email ?? '-', 'amount' => $this->idr($booking->total_price), 'limit' => $this->idr($booking->policy_limit),
                ]),
            ]);
    }

    private function pendingExposure(Company $company): Collection
    {
        $pending = Booking::where('company_id', $company->id)->where('status', 'pending_approval')->get();

        return $pending->isEmpty() ? collect() : collect([[
            'label'  => __('ui.audit_pending_label', ['count' => $pending->count()]),
            'detail' => __('ui.audit_pending_detail', ['amount' => $this->idr($pending->sum('total_price'))]),
        ]]);
    }

    // ---------------------------------------------------------------- helper

    /** Tanpa temuan = lolos; ada temuan = status sesuai jenis pemeriksaan. */
    private function make(string $key, Collection $items, string $failStatus = 'fail'): array
    {
        return ['key' => $key, 'status' => $items->isEmpty() ? 'pass' : $failStatus, 'items' => $items->values()];
    }

    /** "DEP-12" menjadi 12, "DEP-RF-7" menjadi 7 (jika awalannya DEP-RF-). */
    private function txId(?string $reference, string $prefix): ?int
    {
        return $reference && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $reference, $m) ? (int) $m[1] : null;
    }

    private function txLabel(DepositTransaction $tx): string
    {
        return '#' . $tx->id . ' · ' . $tx->created_at->format('d/m/Y H:i') . ' · ' . __('ui.deposit_type_' . $tx->type);
    }

    private function idr(int|float|null $amount): string
    {
        return format_price((int) $amount, 'IDR');
    }
}