<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'all'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $query = Hotel::with(['partner', 'translations'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.hotels.index', [
            'hotels' => $query->get(),
            'status' => $status,
            'counts' => Hotel::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Hotel $hotel)
    {
        $hotel->load(['partner', 'translations', 'facilities', 'roomTypes.translations']);

        return view('admin.hotels.show', compact('hotel'));
    }

    public function approve(Hotel $hotel)
    {
        if (! $hotel->roomTypes()->exists()) {
            return back()->withErrors(['approve' => __('ui.approve_needs_room')]);
        }

        $hotel->update(['status' => 'approved', 'rejection_reason' => null]);

        return redirect()->route('admin.hotels.show', $hotel)->with('success', __('ui.hotel_approved'));
    }

    public function reject(Request $request, Hotel $hotel)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $hotel->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]);

        return redirect()->route('admin.hotels.show', $hotel)->with('success', __('ui.hotel_rejected'));
    }
}