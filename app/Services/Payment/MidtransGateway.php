<?php

namespace App\Services\Payment;

use App\Models\Booking;

class MidtransGateway implements PaymentGateway
{
    public function charge(Booking $booking, string $simulate): array
    {
        throw new \LogicException('Pembayaran Midtrans berjalan lewat Snap dan notifikasi, bukan charge langsung.');
    }

    public function refund(Booking $booking): array
    {
        // Dibuat saat dibutuhkan agar tidak membentuk lingkaran dengan BookingService
        return app(MidtransPayments::class)->refund($booking);
    }
}