<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\StayService;
use Illuminate\Http\Request;

class StayController extends Controller
{
    use PartnerHelpers;

    public function __construct(private StayService $stays)
    {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $all = Booking::where('partner_id', $partner->id)
            ->where('product_type', 'hotel')
            ->where('status', 'confirmed')
            ->where(fn ($q) => $q
                ->where('check_out', '>=', today()->subDays(2))
                ->orWhere(fn ($open) => $open->whereNotNull('checked_in_at')->whereNull('checked_out_at')))
            ->orderBy('check_in')
            ->get();

        return view('partner.stays.index', [
            'arrivals' => $all->filter(fn (Booking $b) => $b->stayStage() === 'upcoming' && $b->check_in->lte(today()) && $b->check_out->gt(today())),
            'tomorrow' => $all->filter(fn (Booking $b) => $b->stayStage() === 'upcoming' && $b->check_in->isSameDay(today()->addDay())),
            'inHouse'  => $all->filter(fn (Booking $b) => $b->stayStage() === 'in_house'),
            'recent'   => $all->filter(fn (Booking $b) => in_array($b->stayStage(), ['departed', 'no_show'], true))
                              ->sortByDesc('updated_at')->take(10),
        ]);
    }

    public function arrive(Request $request, Booking $booking)
    {
        $this->own($request, $booking);
        $this->stays->confirmArrival($booking);

        return back()->with('success', __('ui.stay_arrived_done'));
    }

    public function noShow(Request $request, Booking $booking)
    {
        $this->own($request, $booking);
        $this->stays->markNoShow($booking);

        return back()->with('success', __('ui.stay_no_show_done'));
    }

    public function depart(Request $request, Booking $booking)
    {
        $this->own($request, $booking);
        $this->stays->confirmDeparture($booking);

        return back()->with('success', __('ui.stay_departed_done'));
    }

    private function own(Request $request, Booking $booking): void
    {
        abort_unless($booking->partner_id === $this->partner($request)->id && $booking->product_type === 'hotel', 404);
    }
}