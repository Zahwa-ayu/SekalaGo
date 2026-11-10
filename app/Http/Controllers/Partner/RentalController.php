<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\RentalService;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    use PartnerHelpers;

    public function __construct(private RentalService $rentals)
    {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $stages = Booking::where('partner_id', $partner->id)
            ->where('product_type', 'car')
            ->where('status', 'confirmed')
            ->with('vehicle.translations')
            ->orderBy('check_in')
            ->get()
            ->groupBy(fn (Booking $b) => $b->rentalStage());

        $overdue  = $stages->get('overdue', collect());
        $ongoing  = $stages->get('ongoing', collect());
        $upcoming = $stages->get('upcoming', collect())->filter(fn (Booking $b) => $b->check_in->lte(today()->addDays(2)));
        $returned = $stages->get('returned', collect())->sortByDesc('returned_at')->take(10);

        // Denda yang terhitung saat ini untuk mobil yang masih di tangan penyewa
        $late = $overdue->concat($ongoing)->mapWithKeys(
            fn (Booking $b) => [$b->id => $this->rentals->lateFee($b, now())]
        );

        return view('partner.rentals.index', [
            'conflicts' => $this->rentals->conflicts($partner),
            'overdue'   => $overdue,
            'ongoing'   => $ongoing,
            'upcoming'  => $upcoming,
            'returned'  => $returned,
            'late'      => $late,
        ]);
    }

    public function markPickedUp(Request $request, Booking $booking)
    {
        $this->own($request, $booking);

        $this->rentals->markPickedUp($booking);

        return back()->with('success', __('ui.rental_picked_up_done'));
    }

    public function markReturned(Request $request, Booking $booking)
    {
        $this->own($request, $booking);

        $data = $request->validate([
            'late_fee' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ]);

        $returned = $this->rentals->markReturned(
            $booking,
            isset($data['late_fee']) ? (int) $data['late_fee'] : null
        );

        return back()->with('success', $returned->late_fee > 0
            ? __('ui.rental_returned_with_fee', ['amount' => format_price($returned->late_fee, 'IDR')])
            : __('ui.rental_returned_done'));
    }

    public function cancelAtRisk(Request $request, Booking $booking)
    {
        $this->own($request, $booking);

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $this->rentals->cancelAtRisk($booking, $data['cancellation_reason']);

        return back()->with('success', __('ui.rental_cancelled_refunded', [
            'amount' => format_price($booking->fresh()->refund_amount, 'IDR'),
        ]));
    }

    private function own(Request $request, Booking $booking): void
    {
        abort_unless($booking->partner_id === $this->partner($request)->id && $booking->isCar(), 404);
    }
}