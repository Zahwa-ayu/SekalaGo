<?php

namespace App\Services\Payment;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class MidtransClient
{
    /** Buat transaksi Snap. @return array{token: string, redirect_url: string} */
    public function createSnap(array $payload): array
    {
        $response = $this->http()->post($this->snapBase() . '/transactions', $payload);

        if (! $response->successful() || blank($response->json('token'))) {
            throw new \RuntimeException('Midtrans menolak transaksi: ' . ($response->json('error_messages.0') ?? 'HTTP ' . $response->status()));
        }

        return $response->json();
    }

    /** Status resmi satu transaksi. Transaksi yang belum ada dijawab Midtrans tanpa transaction_status. */
    public function status(string $orderId): array
    {
        return $this->http()->get($this->apiBase() . '/' . rawurlencode($orderId) . '/status')->json() ?? [];
    }

    public function refund(string $orderId, int $amount, string $refundKey, string $reason): array
    {
        return $this->http()->post($this->apiBase() . '/' . rawurlencode($orderId) . '/refund', [
            'refund_key' => $refundKey,
            'amount'     => $amount,
            'reason'     => $reason,
        ])->json() ?? [];
    }

    private function http(): PendingRequest
    {
        $key = config('services.midtrans.server_key');

        if (blank($key)) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env.');
        }

        return Http::withBasicAuth($key, '')->acceptJson()->asJson()->timeout(20);
    }

    private function snapBase(): string
    {
        return config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/v1' : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    private function apiBase(): string
    {
        return config('services.midtrans.is_production') ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
    }
}