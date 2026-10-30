<?php

namespace App\Services;

use App\Models\Booking;
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
    public function __construct(
        private CurrencyService $currencies,
        private PaymentGateway $gateway,
    ) {
    }

    public function create(User $user, RoomType $roomType, Carbon $checkIn, Carbon $checkOut, int $guests, string $guestName): Booking
    {
        $roomType->loadMissing('hotel.translations', 'hotel.partner', 'translations');

        $nights    = (int) $checkIn->diffInDays($checkOut);
        $firstDay  = $checkIn->toDateString();
        $lastNight = $checkOut->copy()->subDay()->toDateString();

        return DB::transaction(function () use ($user, $roomType, $checkOut, $guests, $guestName, $nights, $firstDay, $lastNight) {
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

            return Booking::create([
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
                'status'              => 'pending_payment',
                'payment_status'      => 'unpaid',
                'expires_at'          => now()->addMinutes((int) config('sekalago.booking_hold_minutes')),
                'free_cancel_days'    => $partner->freeCancelDays(),
            ]);
        });
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
     * Batalkan pesanan. Belum dibayar: kamar dilepas saja. Sudah dikonfirmasi: dana dikembalikan
     * penuh lewat gateway, lalu kamar dilepas. Jika refund gagal, pembatalan tidak dilakukan.
     *
     * @param  string  $by  'customer' atau 'admin'
     */
    public function cancel(Booking $booking, string $by, ?string $reason = null): void
    {
        DB::transaction(function () use ($booking, $by, $reason) {
            $fresh = Booking::lockForUpdate()->find($booking->id);

            if (! $fresh || ! in_array($fresh->status, ['pending_payment', 'confirmed'], true)) {
                return;
            }

            $changes = [
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancelled_by'        => $by,
                'cancellation_reason' => $reason,
            ];

            if ($fresh->status === 'confirmed') {
                $result = $this->gateway->refund($fresh);

                if (! $result['success']) {
                    throw ValidationException::withMessages(['refund' => __('ui.refund_failed')]);
                }

                $changes += [
                    'payment_status'   => 'refunded',
                    'refund_amount'    => $fresh->total_price,
                    'refunded_at'      => now(),
                    'refund_reference' => $result['reference'],
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

            if (! $fresh || $fresh->status !== 'pending_payment') {
                return;
            }

            $this->releaseStock($fresh);
            $fresh->update(['status' => $status]);
        });

        $booking->refresh();
    }

    public function expireIfNeeded(Booking $booking): bool
    {
        if ($booking->status === 'pending_payment' && $booking->expires_at?->isPast()) {
            $this->release($booking, 'expired');

            return true;
        }

        return false;
    }

    public function expireOverdue(): int
    {
        $count = 0;

        Booking::where('status', 'pending_payment')
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