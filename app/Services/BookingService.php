<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\RoomStock;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private const HOLDING = ['pending_payment', 'pending_approval'];

    public function __construct(
        private CurrencyService $currencies,
        private PaymentGateway $gateway,
        private DepositService $deposits,
    ) {
    }

    /**
     * Buat pesanan. Tanpa $member: alur pelanggan (bayar lewat gateway).
     * Dengan $member: alur corporate (saldo kantor, limit jabatan, persetujuan Finance).
     */
    public function create(
        User $user,
        RoomType $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        int $guests,
        string $guestName,
        ?CompanyUser $member = null,
    ): Booking {
        $roomType->loadMissing('hotel.translations', 'hotel.partner', 'translations');

        $nights    = (int) $checkIn->diffInDays($checkOut);
        $firstDay  = $checkIn->toDateString();
        $lastNight = $checkOut->copy()->subDay()->toDateString();

        return DB::transaction(function () use ($user, $roomType, $checkOut, $guests, $guestName, $member, $nights, $firstDay, $lastNight) {
            // Kunci baris stok selama transaksi
            $stocks = RoomStock::where('room_type_id', $roomType->id)
                ->whereBetween('date', [$firstDay, $lastNight])
                ->lockForUpdate()
                ->get();

            $soldOut = $stocks->count() !== $nights
                || $stocks->contains(fn ($s) => $s->available <= $s->booked);

            if ($soldOut || $roomType->capacity < $guests) {
                throw ValidationException::withMessages(['room_type' => __('ui.room_unavailable')]);
            }

            RoomStock::where('room_type_id', $roomType->id)
                ->whereBetween('date', [$firstDay, $lastNight])
                ->increment('booked');

            $partner  = $roomType->hotel->partner;
            $currency = $this->currencies->current();
            $total    = $roomType->base_price * $nights;
            $fee      = (int) round($total * $partner->commissionRate());

            $attributes = [
                'booking_code'        => 'SG-' . strtoupper(Str::random(8)),
                'user_id'             => $user->id,
                'partner_id'          => $partner->id,
                'product_type'        => 'hotel',
                'hotel_id'            => $roomType->hotel_id,
                'room_type_id'        => $roomType->id,
                'hotel_name'          => $roomType->hotel->name,
                'room_name'           => $roomType->name,
                'check_in'            => $firstDay,
                'check_out'           => $checkOut->toDateString(),
                'nights'              => $nights,
                'guests'              => $guests,
                'guest_name'          => $guestName,
                'price_per_night'     => $roomType->base_price,
                'total_price'         => $total,
                'platform_fee'        => $fee,
                'vendor_revenue'      => $total - $fee,
                'currency_code'       => $currency->code,
                'exchange_rate'       => $currency->rate_to_base,
                'total_price_display' => round($total / $currency->rate_to_base, $currency->decimal_places),
                'free_cancel_days'    => $partner->freeCancelDays(),
            ];

            if ($member) {
                return $this->createCorporateBooking($user, $member, $attributes, $total);
            }

            return Booking::create($attributes + [
                'status'         => 'pending_payment',
                'payment_status' => 'unpaid',
                'expires_at'     => now()->addMinutes((int) config('sekalago.booking_hold_minutes')),
            ]);
        });
    }

    private function createCorporateBooking(User $user, CompanyUser $member, array $attributes, int $total): Booking
    {
        // Kunci baris karyawan supaya dua pemesanan bersamaan tidak sama-sama lolos cek anggaran
        CompanyUser::lockForUpdate()->find($member->id);

        $member->loadMissing('company', 'position');
        $policy = $member->evaluatePolicy($total);

        $attributes += [
            'company_id'     => $member->company_id,
            'department'     => $member->department,
            'policy_limit'   => $policy['limit'],
            'payment_method' => 'deposit',
        ];

        if ($policy['needs_approval']) {
            // Kamar ditahan, saldo belum dipotong sampai Finance menyetujui
            return Booking::create($attributes + [
                'status'          => 'pending_approval',
                'payment_status'  => 'unpaid',
                'approval_reason' => $policy['reason'],
                'expires_at'      => now()->addHours((int) config('sekalago.approval_hold_hours')),
            ]);
        }

        $booking = Booking::create($attributes + [
            'status'         => 'confirmed',
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ]);

        // Jika saldo tidak cukup, pengecualian ini membatalkan seluruh transaksi termasuk penahanan stok
        $tx = $this->deposits->debit(
            $member->company,
            $total,
            'Pemesanan ' . $booking->booking_code . ' - ' . $booking->hotel_name,
            $booking->booking_code,
            $user
        );

        $booking->update(['payment_reference' => 'DEP-' . $tx->id]);

        return $booking;
    }

    /** Finance menyetujui: saldo kantor dipotong, pesanan terkonfirmasi. */
    public function approve(Booking $booking, User $by, ?string $note = null): void
    {
        DB::transaction(function () use ($booking, $by, $note) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || $fresh->status !== 'pending_approval') {
                throw ValidationException::withMessages(['approval' => __('ui.approval_not_pending')]);
            }

            if ($fresh->expires_at?->isPast()) {
                throw ValidationException::withMessages(['approval' => __('ui.approval_expired')]);
            }

            $tx = $this->deposits->debit(
                Company::findOrFail($fresh->company_id),
                $fresh->total_price,
                'Pemesanan ' . $fresh->booking_code . ' - ' . $fresh->hotel_name,
                $fresh->booking_code,
                $by
            );

            $fresh->update([
                'status'            => 'confirmed',
                'payment_status'    => 'paid',
                'payment_reference' => 'DEP-' . $tx->id,
                'paid_at'           => now(),
                'expires_at'        => null,
                'decided_by'        => $by->id,
                'decided_at'        => now(),
                'decision_note'     => $note,
            ]);
        });

        $booking->refresh();
    }

    /** Finance menolak: kamar dilepas, karyawan melihat catatannya. */
    public function reject(Booking $booking, User $by, string $note): void
    {
        DB::transaction(function () use ($booking, $by, $note) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || $fresh->status !== 'pending_approval') {
                throw ValidationException::withMessages(['approval' => __('ui.approval_not_pending')]);
            }

            $this->releaseStock($fresh);

            $fresh->update([
                'status'        => 'rejected',
                'decided_by'    => $by->id,
                'decided_at'    => now(),
                'decision_note' => $note,
            ]);
        });

        $booking->refresh();
    }

    public function markPaid(Booking $booking, string $reference): void
    {
        DB::transaction(function () use ($booking, $reference) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || $fresh->status !== 'pending_payment') {
                return;
            }

            $fresh->update([
                'status'            => 'confirmed',
                'payment_status'    => 'paid',
                'payment_reference' => $reference,
                'paid_at'           => now(),
            ]);
        });

        $booking->refresh();
    }

    public function markFailed(Booking $booking): void
    {
        $booking->update(['payment_status' => 'failed']);
    }

    /**
     * Batalkan pesanan. Belum dibayar / menunggu persetujuan: kamar dilepas saja.
     * Sudah dikonfirmasi: dana dikembalikan penuh (ke gateway, atau ke saldo kantor untuk
     * pesanan corporate), lalu kamar dilepas. Jika refund gagal, pembatalan tidak dilakukan.
     *
     * @param  string  $by  'customer' atau 'admin'
     */
    public function cancel(Booking $booking, string $by, ?string $reason = null): void
    {
        DB::transaction(function () use ($booking, $by, $reason) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || ! in_array($fresh->status, [...self::HOLDING, 'confirmed'], true)) {
                return;
            }

            $changes = [
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancelled_by'        => $by,
                'cancellation_reason' => $reason,
            ];

            if ($fresh->status === 'confirmed') {
                if ($fresh->payment_method === 'deposit') {
                    $tx = $this->deposits->credit(
                        Company::findOrFail($fresh->company_id),
                        $fresh->total_price,
                        'refund',
                        'Pembatalan ' . $fresh->booking_code,
                        $fresh->booking_code
                    );
                    $reference = 'DEP-RF-' . $tx->id;
                } else {
                    $result = $this->gateway->refund($fresh);

                    if (! $result['success']) {
                        throw ValidationException::withMessages(['refund' => __('ui.refund_failed')]);
                    }

                    $reference = $result['reference'];
                }

                $changes += [
                    'payment_status'   => 'refunded',
                    'refund_amount'    => $fresh->total_price,
                    'refunded_at'      => now(),
                    'refund_reference' => $reference,
                ];
            }

            $this->releaseStock($fresh);
            $fresh->update($changes);
        });

        $booking->refresh();
    }

    /** Lepas kamar yang ditahan dan ubah status pesanan (expired). */
    public function release(Booking $booking, string $status): void
    {
        DB::transaction(function () use ($booking, $status) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || ! in_array($fresh->status, self::HOLDING, true)) {
                return;
            }

            $this->releaseStock($fresh);
            $fresh->update(['status' => $status]);
        });

        $booking->refresh();
    }

    public function expireIfNeeded(Booking $booking): bool
    {
        if (in_array($booking->status, self::HOLDING, true) && $booking->expires_at?->isPast()) {
            $this->release($booking, 'expired');

            return true;
        }

        return false;
    }

    public function expireOverdue(): int
    {
        $count = 0;

        Booking::whereIn('status', self::HOLDING)
            ->where('expires_at', '<', now())
            ->each(function ($booking) use (&$count) {
                $this->release($booking, 'expired');
                $count++;
            });

        return $count;
    }

    private function releaseStock(Booking $booking): void
    {
        if (! $booking->room_type_id) {
            return;
        }

        RoomStock::where('room_type_id', $booking->room_type_id)
            ->whereBetween('date', [
                $booking->check_in->toDateString(),
                $booking->check_out->copy()->subDay()->toDateString(),
            ])
            ->where('booked', '>', 0)
            ->decrement('booked');
    }
}