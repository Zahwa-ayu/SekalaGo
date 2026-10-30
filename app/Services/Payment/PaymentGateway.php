<?php

namespace App\Services\Payment;

use App\Models\Booking;

interface PaymentGateway
{
    /**
     * @param  string  $simulate  'success' atau 'fail' (hanya dipakai gateway sandbox)
     * @return array{success: bool, reference: string}
     */
    public function charge(Booking $booking, string $simulate): array;

    /**
     * Kembalikan dana pesanan secara penuh.
     *
     * @return array{success: bool, reference: string}
     */
    public function refund(Booking $booking): array;
}