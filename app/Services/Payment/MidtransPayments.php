<?php

namespace App\Services\Payment;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MidtransPayments
{
    /** Metode yang refund-nya bisa diminta lewat API; metode lain (VA, transfer bank, gerai) dikembalikan manual. */
    private const REFUNDABLE = ['credit_card', 'gopay', 'shopeepay', 'qris', 'dana'];

    public function __construct(
        private MidtransClient $client,
        private BookingService $bookings,
        private Notifier $notifier,
    ) {
    }

    /** Mulai atau lanjutkan pembayaran: kembalikan transaksi Snap yang masih berlaku, atau buat yang baru. */
    public function start(Booking $booking): Payment
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== 'pending_payment' || $booking->payment_method === 'deposit') {
                $this->fail('payment_not_payable');
            }

            $secondsLeft = (int) now()->diffInSeconds($booking->expires_at, false);

            if ($secondsLeft < 60) {
                $this->fail('payment_time_over');
            }

            $amount   = (int) $booking->total_price;
            $reusable = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->whereNotNull('snap_token')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();

            if ($reusable && $reusable->amount === $amount) {
                return $reusable;
            }

            $orderId = $booking->booking_code . '-' . (Payment::where('booking_id', $booking->id)->count() + 1);
            $minutes = intdiv($secondsLeft, 60);

            $snap = $this->client->createSnap([
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $amount],
                'item_details'        => [[
                    'id'       => $booking->booking_code,
                    'price'    => $amount,
                    'quantity' => 1,
                    'name'     => Str::limit($booking->hotel_name . ' (' . $booking->check_in->format('d/m') . '-' . $booking->check_out->format('d/m') . ')', 50, ''),
                ]],
                'customer_details'    => array_filter([
                    'first_name' => Str::limit($booking->contact_name ?: $booking->guest_name, 100, ''),
                    'email'      => $booking->contact_email ?: $booking->user->email,
                    'phone'      => $booking->contact_phone ?: $booking->user->phone_number,
                ]),
                // Batas bayar di Midtrans sama dengan masa tahan pesanan
                'expiry'              => ['unit' => 'minutes', 'duration' => $minutes],
                'callbacks'           => ['finish' => route('bookings.show', $booking)],
                'custom_field1'       => $booking->booking_code,
            ]);

            return Payment::create([
                'booking_id'   => $booking->id,
                'order_id'     => $orderId,
                'amount'       => $amount,
                'status'       => 'pending',
                'snap_token'   => $snap['token'],
                'redirect_url' => $snap['redirect_url'] ?? null,
                'expires_at'   => now()->addMinutes($minutes),
            ]);
        });
    }

    /** Notifikasi dari Midtrans: periksa signature, lalu ambil status resmi dari Midtrans (bukan dari isi notifikasi). */
    public function handleNotification(array $payload): void
    {
        $orderId  = (string) ($payload['order_id'] ?? '');
        $expected = hash('sha512', $orderId . ($payload['status_code'] ?? '') . ($payload['gross_amount'] ?? '') . config('services.midtrans.server_key'));

        abort_unless(hash_equals($expected, (string) ($payload['signature_key'] ?? '')), 403);

        $payment = Payment::where('order_id', $orderId)->first();

        // Tes notifikasi dari dashboard Midtrans memakai nomor order contoh: cukup dijawab 200
        if ($payment) {
            $this->sync($payment);
        }
    }

    /** Tanya status ke Midtrans lalu terapkan. Dipakai webhook dan pengecekan di halaman bayar. */
    public function sync(Payment $payment): Payment
    {
        $status = $this->client->status($payment->order_id);

        if (filled($status['transaction_status'] ?? null)) {
            $this->apply($payment, $status);
        }

        return $payment->refresh();
    }

    /**
     * Refund untuk pembatalan. Dipanggil BookingService::cancel lewat MidtransGateway.
     * Metode yang bisa di-refund lewat API diminta otomatis; sisanya (atau yang gagal) dicatat sebagai refund manual.
     *
     * @return array{success: bool, reference: ?string}
     */
    public function refund(Booking $booking): array
    {
        $payment = Payment::where('booking_id', $booking->id)->where('status', 'paid')->latest('id')->first();
        $manual  = ['success' => true, 'reference' => 'MANUAL-' . $booking->booking_code];

        // Dibayar sebelum Midtrans dipakai (mode simulasi): tidak ada transaksi yang bisa di-refund lewat API
        if (! $payment) {
            $this->flagManualRefund(null, $booking, (int) $booking->total_price);

            return $manual;
        }

        if (in_array($payment->payment_type, self::REFUNDABLE, true)) {
            try {
                $result = $this->client->refund(
                    $payment->order_id,
                    (int) $payment->amount,
                    'RF-' . $payment->id . '-' . now()->timestamp,
                    'Pembatalan ' . $booking->booking_code
                );

                if (($result['status_code'] ?? null) === '200') {
                    $payment->update([
                        'status'           => 'refunded',
                        'refund_status'    => 'done',
                        'refund_amount'    => $payment->amount,
                        'refund_reference' => $result['refund_key'] ?? null,
                        'refunded_at'      => now(),
                    ]);

                    return ['success' => true, 'reference' => 'MT-RF-' . $payment->id];
                }

                report(new \RuntimeException('Refund Midtrans ditolak untuk ' . $payment->order_id . ': ' . json_encode($result)));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->flagManualRefund($payment, $booking, (int) $payment->amount);

        return $manual;
    }

    // ---------------------------------------------------------------- internal

    private function apply(Payment $payment, array $s): void
    {
        DB::transaction(function () use ($payment, $s) {
            $p      = Payment::lockForUpdate()->findOrFail($payment->id);
            $status = (string) $s['transaction_status'];
            $fraud  = $s['fraud_status'] ?? null;

            $p->fill([
                'payment_type'   => $s['payment_type'] ?? $p->payment_type,
                'transaction_id' => $s['transaction_id'] ?? $p->transaction_id,
                'last_payload'   => $s,
            ]);

            $settled = $status === 'settlement' || ($status === 'capture' && $fraud === 'accept');

            if ($settled) {
                if (! in_array($p->status, ['paid', 'refunded'], true)) {
                    $this->settle($p, $s);
                }
            } elseif (in_array($status, ['deny', 'cancel', 'failure'], true) && $p->status === 'pending') {
                $p->status = 'failed';
                $booking   = Booking::find($p->booking_id);

                if ($booking && $booking->status === 'pending_payment') {
                    $this->bookings->markFailed($booking);
                }
            } elseif ($status === 'expire' && $p->status === 'pending') {
                $p->status = 'expired';
            } elseif (in_array($status, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true) && $p->status === 'paid') {
                // Dilakukan langsung lewat dashboard Midtrans
                $p->status = 'refunded';
            }

            $p->save();
        });
    }

    private function settle(Payment $p, array $s): void
    {
        $paid = (int) round((float) ($s['gross_amount'] ?? 0));

        if ($paid !== (int) $p->amount) {
            report(new \RuntimeException("Jumlah Midtrans tidak cocok untuk {$p->order_id}: {$paid} vs {$p->amount}"));
            $this->notifier->to($this->notifier->admins(), 'payment_mismatch', ['order' => $p->order_id], route('admin.payments.index'), 'fa-triangle-exclamation');

            return;
        }

        $p->fill([
            'status'             => 'paid',
            'paid_at'            => now(),
            'gateway_fee'        => app(GatewayFee::class)->estimate($p->payment_type, (int) $p->amount),
            'gateway_fee_source' => 'estimate',
        ]);

        $booking = Booking::lockForUpdate()->find($p->booking_id);

        if ($booking && $booking->status === 'pending_payment') {
            $this->bookings->markPaid($booking, $p->order_id);
            $booking->update(['payment_method' => $p->payment_type]);

            return;
        }

        // Dana masuk untuk pesanan yang sudah kedaluwarsa atau dibatalkan: harus dikembalikan
        $p->fill(['refund_status' => 'manual_pending', 'refund_amount' => $p->amount]);

        $this->notifier->to($this->notifier->admins(), 'payment_orphan', [
            'order'  => $p->order_id,
            'amount' => format_price($p->amount, 'IDR'),
        ], route('admin.payments.index', ['view' => 'refund']), 'fa-triangle-exclamation');
    }

    private function flagManualRefund(?Payment $payment, Booking $booking, int $amount): void
    {
        $payment?->update(['refund_status' => 'manual_pending', 'refund_amount' => $amount]);

        $this->notifier->to($this->notifier->admins(), 'refund_manual', [
            'code'   => $booking->booking_code,
            'amount' => format_price($amount, 'IDR'),
            'method' => $payment?->payment_type ?: '-',
        ], route('admin.payments.index', ['view' => 'refund']), 'fa-rotate-left');
    }

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['payment' => __('ui.' . $key)]);
    }
}