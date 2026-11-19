<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payment\GatewayFee;
use Illuminate\Console\Command;

class EstimatePaymentFees extends Command
{
    protected $signature = 'payments:estimate-fees {--force : hitung ulang juga yang masih berstatus perkiraan}';

    protected $description = 'Isi perkiraan biaya gateway untuk pembayaran yang sudah lunas';

    public function handle(GatewayFee $fees): int
    {
        $query = Payment::query()
            ->whereIn('status', ['paid', 'refunded'])
            ->where(fn ($q) => $q
                ->whereNull('gateway_fee_source')
                ->when($this->option('force'), fn ($w) => $w->orWhere('gateway_fee_source', 'estimate')));

        $count = 0;

        foreach ($query->cursor() as $payment) {
            $payment->update([
                'gateway_fee'        => $fees->estimate($payment->payment_type, (int) $payment->amount),
                'gateway_fee_source' => 'estimate',
            ]);
            $count++;
        }

        $this->info("{$count} pembayaran diperbarui.");

        return self::SUCCESS;
    }
}