<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews)
    {
    }

    public function create(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $state = $this->reviews->eligibility($booking, auth()->user());

        if ($state !== 'ok') {
            return redirect()->route('bookings.show', $booking)->with('error', __('ui.review_not_allowed_' . $state));
        }

        return view('reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->reviews->create($booking, $request->user(), (int) $data['rating'], $data['comment'] ?? null);

        return redirect()->route('bookings.show', $booking)->with('success', __('ui.review_thanks'));
    }
}