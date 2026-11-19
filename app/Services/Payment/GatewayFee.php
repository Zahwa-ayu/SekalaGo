<?php

namespace App\Services\Payment;

class GatewayFee
{
    /** Perkiraan biaya gateway (IDR, sudah termasuk PPN) untuk satu pembayaran. */
    public function estimate(?string $paymentType, int $amount): int
    {
        $rates = config('sekalago.gateway_fees');
        $rate  = $rates[$paymentType] ?? $rates['default'];

        $fee = (float) $rate['fixed'] + $amount * ((float) $rate['percent'] / 100);
        $vat = (float) config('sekalago.gateway_vat_percent', 0) / 100;

        return (int) round($fee * (1 + $vat));
    }
}