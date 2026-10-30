<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private const STATUSES = ['confirmed', 'pending_payment', 'cancelled', 'expired', 'all'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'confirmed');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $summary = Booking::where('status', 'confirmed')
            ->selectRaw('count(*) as orders, coalesce(sum(total_price), 0) as gross, coalesce(sum(platform_fee), 0) as fee, coalesce(sum(vendor_revenue), 0) as net')
            ->first();

        $bookings = Booking::with('partner')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.bookings.index', compact('summary', 'bookings', 'status'));
    }

    /** Pembatalan operasional oleh admin: refund penuh, alasan wajib diisi. */
    public function cancel(Request $request, Booking $booking, BookingService $bookings)
    {
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $booking->canBeCancelledByAdmin()) {
            return back()->with('error', __('ui.cancel_not_allowed_admin'));
        }

        $bookings->cancel($booking, 'admin', $data['cancellation_reason']);

        return back()->with('success', __('ui.booking_cancelled_refunded', [
            'amount' => format_price($booking->refund_amount, 'IDR'),
        ]));
    }
}