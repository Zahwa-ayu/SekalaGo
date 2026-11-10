<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\ExtensionService;
use Illuminate\Http\Request;

class BookingExtensionController extends Controller
{
    public function __construct(private ExtensionService $extensions)
    {
    }

    public function create(Request $request, Booking $booking)
    {
        $this->own($booking);

        if (! $this->extensions->canExtend($booking)) {
            return redirect()->route('bookings.show', $booking)
                ->withErrors(['extension' => __('ui.extension_not_allowed')]);
        }

        $days = max(1, min(ExtensionService::MAX_EXTENSION_DAYS, (int) $request->query('days', 1)));

        return view('bookings.extend', [
            'booking'   => $booking,
            'days'      => $days,
            'quote'     => $this->extensions->quote($booking, $days),
            'isDeposit' => $booking->payment_method === 'deposit',
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $this->own($booking);

        $data = $request->validate([
            'days'    => ['required', 'integer', 'between:1,' . ExtensionService::MAX_EXTENSION_DAYS],
            'outcome' => ['nullable', 'in:success,fail'],
        ]);

        $extension = $this->extensions->extend(
            $booking,
            (int) $data['days'],
            $request->user(),
            $request->user()->companyUser,
            $data['outcome'] ?? 'success'
        );

        return redirect()->route('bookings.show', $booking)->with('success', __('ui.extension_done', [
            'date'   => $extension->new_check_out->format('d/m/Y'),
            'amount' => format_price($extension->amount, 'IDR'),
        ]));
    }

    /** Hanya pemilik pesanan, dan hanya pesanan rental mobil. */
    private function own(Booking $booking): void
    {
        abort_unless($booking->user_id === auth()->id(), 403);
        abort_unless($booking->isCar(), 404);
    }
}