<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Support\Str;

/** Gateway palsu untuk pengembangan. Ganti dengan Midtrans/Xendit sandbox nanti. */
class MockPaymentGateway implements PaymentGateway
{
    public function charge(Booking $booking, string $simulate): array
    {
        return [
            'success'   => $simulate === 'success',
            'reference' => 'SBX-' . strtoupper(Str::random(10)),
        ];
    }

    public function refund(Booking $booking): array
    {
        return [
            'success'   => true,
            'reference' => 'SBX-RF-' . strtoupper(Str::random(10)),
        ];
    }
}