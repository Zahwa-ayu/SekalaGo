<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StayService
{
    /** Hotel mengonfirmasi tamu sudah tiba. Juga membatalkan catatan "tidak hadir" jika tamu ternyata datang. */
    public function confirmArrival(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $stay = $this->locked($booking);

            if ($stay->checked_in_at) {
                $this->fail('stay_already_arrived');
            }

            if ($stay->check_in->gt(today())) {
                $this->fail('stay_too_early');
            }

            if ($stay->check_out->lte(today())) {
                $this->fail('stay_period_over');
            }

            $stay->update(['checked_in_at' => now(), 'no_show_at' => null]);

            return $stay;
        });
    }

    /** Tamu tidak datang: dana tidak dikembalikan, kamar tetap terjual. */
    public function markNoShow(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $stay = $this->locked($booking);

            if ($stay->checked_in_at) {
                $this->fail('stay_already_arrived');
            }

            if ($stay->no_show_at) {
                $this->fail('stay_already_no_show');
            }

            if ($stay->check_in->gt(today())) {
                $this->fail('stay_too_early');
            }

            if ($stay->payout_id) {
                $this->fail('stay_locked_payout');
            }

            $stay->update(['no_show_at' => now()]);

            return $stay;
        });
    }

    public function confirmDeparture(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $stay = $this->locked($booking);

            if (! $stay->checked_in_at) {
                $this->fail('stay_not_arrived');
            }

            if ($stay->checked_out_at) {
                $this->fail('stay_already_departed');
            }

            $stay->update(['checked_out_at' => now()]);

            return $stay;
        });
    }

    private function locked(Booking $booking): Booking
    {
        $stay = Booking::lockForUpdate()->findOrFail($booking->id);

        if ($stay->product_type !== 'hotel') {
            $this->fail('stay_not_hotel');
        }

        if ($stay->status !== 'confirmed') {
            $this->fail('rental_not_confirmed');
        }

        return $stay;
    }

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['booking' => __('ui.' . $key)]);
    }
}