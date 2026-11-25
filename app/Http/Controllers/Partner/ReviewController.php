<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use PartnerHelpers;

    public function __construct(private ReviewService $reviews)
    {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $view   = (string) $request->query('view', '');
        $type   = (string) $request->query('type', '');
        $rating = (int) $request->query('rating', 0);

        abort_unless(in_array($view, ['', 'unreplied', 'replied', 'hidden'], true), 404);
        abort_unless(in_array($type, ['', 'hotel', 'car'], true), 404);

        $base = Review::where('partner_id', $partner->id);

        $reviews = (clone $base)->with('booking')
            ->search($request->query('q'), ['comment', 'reviewer_name', 'booking.booking_code'])
            ->when($view === 'unreplied', fn ($q) => $q->whereNull('partner_reply'))
            ->when($view === 'replied', fn ($q) => $q->whereNotNull('partner_reply'))
            ->when($view === 'hidden', fn ($q) => $q->where('is_visible', false))
            ->when($rating >= 1 && $rating <= 5, fn ($q) => $q->where('rating', $rating))
            ->when($type === 'hotel', fn ($q) => $q->whereNotNull('hotel_id'))
            ->when($type === 'car', fn ($q) => $q->whereNotNull('vehicle_id'))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('partner.reviews.index', [
            'reviews' => $reviews,
            'average' => round((float) Review::visible()->where('partner_id', $partner->id)->avg('rating'), 1),
            'view'    => $view,
            'type'    => $type,
            'rating'  => $rating,
            'counts'  => [
                ''          => (clone $base)->count(),
                'unreplied' => (clone $base)->whereNull('partner_reply')->count(),
                'replied'   => (clone $base)->whereNotNull('partner_reply')->count(),
                'hidden'    => (clone $base)->where('is_visible', false)->count(),
            ],
        ]);
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate(['partner_reply' => ['required', 'string', 'max:1000']]);

        $this->reviews->reply($review, $this->partner($request), $data['partner_reply']);

        return back()->with('success', __('ui.review_reply_saved'));
    }
}