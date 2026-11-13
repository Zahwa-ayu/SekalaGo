<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerAgreement;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PayoutService
{
    /**
     * Pesanan yang sudah boleh dicairkan: terkonfirmasi, belum masuk pencairan mana pun, dan layanannya selesai.
     * Hotel: sesudah check-out. Mobil: sesudah dikembalikan; yang tidak pernah ditandai dikembalikan menunggu lebih lama.
     */
    public function eligibleQuery(): Builder
    {
        $cutoff = today()->subDays((int) config('sekalago.payout_hold_days', 1))->toDateString();
        $stale  = today()->subDays((int) config('sekalago.payout_unmarked_car_days', 7))->toDateString();

        return Booking::query()
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.payout_id')
            ->where('bookings.vendor_revenue', '>', 0)
            ->where(function (Builder $q) use ($cutoff, $stale) {
                $q->where(fn ($hotel) => $hotel
                        ->where('bookings.product_type', 'hotel')
                        ->where('bookings.check_out', '<=', $cutoff))
                  ->orWhere(fn ($car) => $car
                        ->where('bookings.product_type', 'car')
                        ->where(fn ($w) => $w
                            ->where(fn ($returned) => $returned
                                ->whereNotNull('bookings.returned_at')
                                ->whereRaw('DATE(bookings.returned_at) <= ?', [$cutoff]))
                            ->orWhere(fn ($unmarked) => $unmarked
                                ->whereNull('bookings.returned_at')
                                ->where('bookings.check_out', '<=', $stale))));
            });
    }

    /**
     * Ringkasan per mitra terverifikasi: yang siap dicairkan, yang masih menunggu, jadwal, dan status.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function overview(?int $partnerId = null): Collection
    {
        $eligible = $this->eligibleQuery()
            ->when($partnerId, fn ($q, $id) => $q->where('bookings.partner_id', $id))
            ->selectRaw("bookings.partner_id, COUNT(*) AS n, SUM(bookings.vendor_revenue) AS amount, SUM(bookings.platform_fee) AS fee,
                         MIN(CASE WHEN bookings.product_type = 'hotel' THEN bookings.check_out ELSE COALESCE(DATE(bookings.returned_at), bookings.check_out) END) AS first_date")
            ->groupBy('bookings.partner_id')
            ->get()
            ->keyBy('partner_id');

        $unpaid = Booking::query()
            ->where('status', 'confirmed')
            ->whereNull('payout_id')
            ->where('vendor_revenue', '>', 0)
            ->when($partnerId, fn ($q, $id) => $q->where('partner_id', $id))
            ->selectRaw('partner_id, SUM(vendor_revenue) AS amount')
            ->groupBy('partner_id')
            ->pluck('amount', 'partner_id');

        $last = Payout::query()
            ->where('status', '!=', 'cancelled')
            ->when($partnerId, fn ($q, $id) => $q->where('partner_id', $id))
            ->selectRaw('partner_id, MAX(created_at) AS last_at')
            ->groupBy('partner_id')
            ->pluck('last_at', 'partner_id');

        $schedules = PartnerAgreement::query()
            ->where('status', 'signed')
            ->orderBy('version')
            ->get()
            ->groupBy('partner_id')
            ->map(fn ($agreements) => $agreements->last()->payout_schedule);

        $minimum = (int) config('sekalago.payout_minimum', 100000);

        return Partner::query()
            ->where('verification_status', 'verified')
            ->when($partnerId, fn ($q, $id) => $q->whereKey($id))
            ->orderBy('company_name')
            ->get()
            ->map(function (Partner $partner) use ($eligible, $unpaid, $last, $schedules, $minimum) {
                $row      = $eligible->get($partner->id);
                $amount   = (int) ($row->amount ?? 0);
                $waiting  = max(0, (int) $unpaid->get($partner->id, 0) - $amount);
                $schedule = $schedules->get($partner->id, 'monthly');
                $lastAt   = $last->has($partner->id) ? Carbon::parse($last->get($partner->id)) : null;
                $base     = $lastAt ?? ($row ? Carbon::parse($row->first_date) : null);
                $nextDue  = $base ? $this->addInterval($base, $schedule) : null;

                $state = match (true) {
                    $amount === 0                      => 'none',
                    blank($partner->bank_account_no)   => 'no_bank',
                    $amount < $minimum                 => 'below_minimum',
                    $nextDue && $nextDue->isFuture()   => 'not_due',
                    default                            => 'due',
                };

                return [
                    'partner'  => $partner,
                    'schedule' => $schedule,
                    'eligible' => $amount,
                    'count'    => (int) ($row->n ?? 0),
                    'fee'      => (int) ($row->fee ?? 0),
                    'waiting'  => $waiting,
                    'last_at'  => $lastAt,
                    'next_due' => $nextDue,
                    'state'    => $state,
                ];
            });
    }

    /** Buat pencairan dari semua pesanan mitra yang sudah boleh dicairkan. */
    public function create(Partner $partner, User $by, ?string $note = null): Payout
    {
        if (blank($partner->bank_account_no)) {
            $this->fail('payout_no_bank');
        }

        return DB::transaction(function () use ($partner, $by, $note) {
            $bookings = $this->eligibleQuery()
                ->where('bookings.partner_id', $partner->id)
                ->lockForUpdate()
                ->get(['bookings.id', 'bookings.vendor_revenue', 'bookings.platform_fee']);

            if ($bookings->isEmpty()) {
                $this->fail('payout_nothing');
            }

            $schedule = PartnerAgreement::where('partner_id', $partner->id)
                ->where('status', 'signed')
                ->orderByDesc('version')
                ->value('payout_schedule');

            $payout = Payout::create([
                'code'               => $this->newCode(),
                'partner_id'         => $partner->id,
                'status'             => 'pending',
                'total_amount'       => (int) $bookings->sum('vendor_revenue'),
                'platform_fee_total' => (int) $bookings->sum('platform_fee'),
                'booking_count'      => $bookings->count(),
                'bank_account'       => $partner->bank_account_no,
                'schedule'           => $schedule,
                'note'               => $note,
                'created_by'         => $by->id,
            ]);

            Booking::whereIn('id', $bookings->pluck('id'))->update(['payout_id' => $payout->id]);

            return $payout;
        });
    }

    /**
     * Buat pencairan untuk semua mitra yang sudah jatuh tempo.
     *
     * @return array{created: int, total: int}
     */
    public function generateDue(User $by): array
    {
        $created = 0;
        $total   = 0;

        foreach ($this->overview()->where('state', 'due') as $row) {
            try {
                $payout = $this->create($row['partner'], $by);
                $created++;
                $total += $payout->total_amount;
            } catch (ValidationException) {
                // dilewati: kondisinya berubah sejak daftar dibuat
            }
        }

        return ['created' => $created, 'total' => $total];
    }

    /** Admin sudah mentransfer lewat bank dan mencatat referensinya. */
    public function markPaid(Payout $payout, User $by, string $reference, ?Carbon $paidAt = null): Payout
    {
        return DB::transaction(function () use ($payout, $by, $reference, $paidAt) {
            $fresh = Payout::lockForUpdate()->findOrFail($payout->id);

            if ($fresh->status !== 'pending') {
                $this->fail('payout_not_pending');
            }

            $fresh->update([
                'status'             => 'paid',
                'transfer_reference' => $reference,
                'paid_at'            => $paidAt ?? now(),
                'paid_by'            => $by->id,
            ]);

            return $fresh;
        });
    }

    /** Batalkan pencairan yang belum ditransfer; pesanannya bisa dicairkan lagi. */
    public function cancel(Payout $payout, User $by, string $reason): Payout
    {
        return DB::transaction(function () use ($payout, $by, $reason) {
            $fresh = Payout::lockForUpdate()->findOrFail($payout->id);

            if ($fresh->status !== 'pending') {
                $this->fail('payout_not_pending');
            }

            Booking::where('payout_id', $fresh->id)->update(['payout_id' => null]);

            $fresh->update(['status' => 'cancelled', 'note' => $reason, 'paid_by' => $by->id]);

            return $fresh;
        });
    }

    private function addInterval(Carbon $from, string $schedule): Carbon
    {
        return match ($schedule) {
            'weekly'   => $from->copy()->addWeek(),
            'biweekly' => $from->copy()->addWeeks(2),
            default    => $from->copy()->addMonthNoOverflow(),
        };
    }

    private function newCode(): string
    {
        do {
            $code = 'PO-' . now()->format('ym') . '-' . strtoupper(Str::random(5));
        } while (Payout::where('code', $code)->exists());

        return $code;
    }

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['payout' => __('ui.' . $key)]);
    }
}