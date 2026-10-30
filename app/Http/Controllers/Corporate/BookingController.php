<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use CorporateHelpers;

    private const STATUSES = ['pending_approval', 'confirmed', 'rejected', 'cancelled', 'expired', 'all'];

    public function __construct(private BookingService $bookings)
    {
    }

    /** Pesanan seluruh karyawan perusahaan; bawaan: antrean persetujuan. */
    public function index(Request $request)
    {
        $company = $this->company($request);

        $status = $request->query('status', 'pending_approval');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $this->bookings->expireOverdue();

        $bookings = Booking::where('company_id', $company->id)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with('user.companyUser')
            ->latest()
            ->get();

        $counts = Booking::where('company_id', $company->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('corporate.bookings.index', compact('bookings', 'status', 'counts'));
    }

    public function approve(Request $request, Booking $booking)
    {
        $this->ownBooking($request, $booking);

        if ($this->bookings->expireIfNeeded($booking)) {
            return back()->with('error', __('ui.approval_expired'));
        }

        $this->bookings->approve($booking, $request->user());

        return back()->with('success', __('ui.approval_done'));
    }

    public function reject(Request $request, Booking $booking)
    {
        $this->ownBooking($request, $booking);

        $data = $request->validate([
            'decision_note' => ['required', 'string', 'max:500'],
        ]);

        $this->bookings->reject($booking, $request->user(), $data['decision_note']);

        return back()->with('success', __('ui.approval_rejected_msg'));
    }

    private function ownBooking(Request $request, Booking $booking): void
    {
        abort_unless($booking->company_id === $this->company($request)->id, 404);
    }
}