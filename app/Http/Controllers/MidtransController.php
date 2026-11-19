<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\Payment\MidtransPayments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MidtransController extends Controller
{
    public function __construct(private MidtransPayments $payments, private BookingService $bookings)
    {
    }

    /** Token Snap untuk popup pembayaran. */
    public function snap(Request $request, Booking $booking): JsonResponse
    {
        $this->guard($booking);

        if ($this->bookings->expireIfNeeded($booking)) {
            return response()->json(['message' => __('ui.payment_time_over')], 422);
        }

        try {
            $payment = $this->payments->start($booking);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('ui.payment_gateway_error')], 502);
        }

        return response()->json([
            'token'        => $payment->snap_token,
            'order_id'     => $payment->order_id,
            'redirect_url' => $payment->redirect_url,
        ]);
    }

    /** Status pesanan, dengan pengecekan langsung ke Midtrans selagi pembayaran masih menunggu. */
    public function status(Request $request, Booking $booking): JsonResponse
    {
        $this->guard($booking);

        $payment = Payment::where('booking_id', $booking->id)->latest('id')->first();

        if ($booking->status === 'pending_payment' && $payment && $payment->status === 'pending') {
            try {
                $this->payments->sync($payment);
            } catch (\Throwable $e) {
                report($e);   // pengecekan ini hanya pelengkap webhook
            }
        }

        return response()->json([
            'status'  => $booking->fresh()->status,
            'payment' => $payment?->fresh()->status,
        ]);
    }

    private function guard(Booking $booking): void
    {
        abort_unless(config('sekalago.payment_driver') === 'midtrans', 404);
        abort_unless($booking->user_id === auth()->id(), 403);
        abort_if($booking->payment_method === 'deposit', 404);
    }
}