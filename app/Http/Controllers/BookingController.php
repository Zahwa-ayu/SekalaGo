<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\Payment\PaymentGateway;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\BookingDetails;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const MAX_NIGHTS = 30;

    public function __construct(
        private BookingService $bookings,
        private PaymentGateway $gateway,
    ) {
    }

    public function index(Request $request)
    {
        $this->bookings->expireOverdue();

        $product = $request->query('product');
        abort_unless(in_array($product, [null, '', 'hotel', 'car'], true), 404);

        $list = Booking::where('user_id', $request->user()->id)
            ->when($product, fn ($q, $type) => $q->where('product_type', $type))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('bookings.index', ['bookings' => $list]);
    }

    /** Ringkasan pesanan sebelum dikonfirmasi. */
    public function create(Request $request)
    {
        [$roomType, $checkIn, $checkOut, $guests, $nights] = $this->resolveStay($request);

        $member = $this->member($request);
        $total  = $roomType->base_price * $nights;
        $policy = $member?->evaluatePolicy($total);

        return view('bookings.create', [
            'roomType'      => $roomType,
            'checkIn'       => $checkIn,
            'checkOut'      => $checkOut,
            'guests'        => $guests,
            'nights'        => $nights,
            'total'         => $total,
            'isOpen'        => $roomType->open_nights === $nights && $roomType->capacity >= $guests,
            'isCorporate'   => $member !== null,
            'isFinance'     => $member?->isFinance() ?? false,
            'policy'        => $policy,
            'limit'         => $policy['limit'] ?? null,
            'needsApproval' => $policy['needs_approval'] ?? false,
        ]);
    }

    public function store(Request $request)
    {
        $member = $this->member($request);
        $data   = $request->validate(BookingDetails::rules('hotel', $member !== null));

        [$roomType, $checkIn, $checkOut, $guests] = $this->resolveStay($request);

        $booking = $this->bookings->create(
            $request->user(),
            $roomType,
            $checkIn,
            $checkOut,
            $guests,
            $data['guest_name'],
            $member,
            BookingDetails::attributes($data)
        );

        BookingDetails::rememberContact($request->user(), $data);

        if ($member) {
            $message = $booking->status === 'confirmed'
                ? __('ui.booking_confirmed_deposit')
                : __('ui.booking_submitted');

            return redirect()->route('bookings.show', $booking)->with('success', $message);
        }

        return redirect()->route('bookings.pay', $booking);
    }

    public function show(Booking $booking)
    {
        $this->own($booking);
        $this->bookings->expireIfNeeded($booking);

        return view('bookings.show', compact('booking'));
    }

    public function pay(Booking $booking)
    {
        $this->own($booking);
        abort_if($booking->isCorporate(), 404);

        if ($this->bookings->expireIfNeeded($booking)) {
            return redirect()->route('bookings.show', $booking)->with('error', __('ui.booking_expired'));
        }

        if ($booking->status !== 'pending_payment') {
            return redirect()->route('bookings.show', $booking);
        }

        return view('bookings.pay', compact('booking'));
    }

    public function processPayment(Request $request, Booking $booking)
    {
        // Simulasi bayar hanya boleh di mode mock dan bukan di production.
        // Tanpa ini, pesanan bisa dikonfirmasi tanpa membayar saat mode Midtrans aktif.
        abort_if(config('sekalago.payment_driver') !== 'mock' || app()->isProduction(), 404);

        $data = $request->validate(['outcome' => ['required', 'in:success,fail']]);

        $this->own($booking);
        abort_if($booking->isCorporate(), 404);

        if ($this->bookings->expireIfNeeded($booking)) {
            return redirect()->route('bookings.show', $booking)->with('error', __('ui.booking_expired'));
        }

        if ($booking->status !== 'pending_payment') {
            return redirect()->route('bookings.show', $booking);
        }

        $result = $this->gateway->charge($booking, $data['outcome']);

        if ($result['success']) {
            $this->bookings->markPaid($booking, $result['reference']);

            return redirect()->route('bookings.show', $booking)->with('success', __('ui.payment_success'));
        }

        $this->bookings->markFailed($booking);

        return back()->with('error', __('ui.payment_failed'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->own($booking);

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
            'accept_fee'          => ['nullable', 'boolean'],
        ]);

        $quote = $booking->cancellationQuote();

        if ($quote['type'] === 'none') {
            return back()->with('error', __('ui.cancel_not_allowed'));
        }

        // Pembatalan berdenda butuh persetujuan eksplisit supaya tidak terjadi tanpa sengaja
        if ($quote['type'] === 'fee' && ! $request->boolean('accept_fee')) {
            return back()->with('error', __('ui.cancel_fee_accept_required'));
        }

        $wasPaid = $booking->status === 'confirmed';

        $this->bookings->cancel($booking, 'customer', $data['cancellation_reason'] ?? null, applyFee: true);

        $message = match (true) {
            $wasPaid && $booking->cancel_fee > 0 => __('ui.booking_cancelled_with_fee', [
                'fee'    => format_price($booking->cancel_fee, 'IDR'),
                'amount' => format_price($booking->refund_amount, 'IDR'),
            ]),
            $wasPaid => __('ui.booking_cancelled_refunded', ['amount' => format_price($booking->refund_amount, 'IDR')]),
            default  => __('ui.booking_cancelled'),
        };

        return redirect()->route('bookings.show', $booking)->with('success', $message);
    }

    public function ticket(Booking $booking)
    {
        $this->own($booking);
        abort_unless($booking->status === 'confirmed', 404);

        $booking->loadMissing('hotel');

        return Pdf::loadView($booking->isCar() ? 'bookings.ticket-car' : 'bookings.ticket', [
            'booking'  => $booking,
            'platform' => config('app.name'),
        ])->setPaper('a4')->download('E-Ticket-' . $booking->booking_code . '.pdf');
    }

    private function own(Booking $booking): void
    {
        abort_unless($booking->user_id === auth()->id(), 403);
    }

    /** Data karyawan jika pemesan adalah user corporate; null untuk pelanggan biasa. */
    private function member(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'corporate') {
            return null;
        }

        $member = $user->companyUser;
        abort_unless($member, 403);

        return $member->loadMissing('company', 'position');
    }

    /** @return array{0: RoomType, 1: Carbon, 2: Carbon, 3: int, 4: int} */
    private function resolveStay(Request $request): array
    {
        $data = $request->validate([
            'room_type' => ['required', 'integer', 'exists:room_types,id'],
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['required', 'integer', 'between:1,20'],
        ]);

        $checkIn  = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();
        $nights   = (int) $checkIn->diffInDays($checkOut);

        if ($nights > self::MAX_NIGHTS) {
            throw ValidationException::withMessages([
                'check_out' => __('ui.max_nights', ['count' => self::MAX_NIGHTS]),
            ]);
        }

        $roomType = RoomType::withOpenNights($checkIn, $checkOut)
            ->with('hotel.translations', 'translations')
            ->findOrFail($data['room_type']);

        abort_unless($roomType->hotel->status === 'approved', 404);

        return [$roomType, $checkIn, $checkOut, (int) $data['guests'], $nights];
    }
}