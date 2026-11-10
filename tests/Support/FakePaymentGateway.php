<?php

namespace Tests\Support;

use App\Models\Booking;
use App\Services\Payment\PaymentGateway;

class FakePaymentGateway implements PaymentGateway
{
    public int $charges = 0;
    public int $refunds = 0;
    public ?int $lastChargeAmount = null;
    public bool $refundSucceeds = true;

    public function charge(Booking $booking, string $simulate): array
    {
        $this->charges++;
        $this->lastChargeAmount = (int) $booking->total_price;

        return ['success' => $simulate === 'success', 'reference' => 'FAKE-CHARGE-' . $this->charges];
    }

    public function refund(Booking $booking): array
    {
        $this->refunds++;

        return ['success' => $this->refundSucceeds, 'reference' => 'FAKE-REFUND-' . $this->refunds];
    }
}