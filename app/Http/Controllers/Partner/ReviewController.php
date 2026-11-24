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

        return view('partner.reviews.index', [
            'reviews' => Review::with('booking')->where('partner_id', $partner->id)->latest()
                ->paginate((int) config('sekalago.per_page', 15)),
            'average' => round((float) Review::visible()->where('partner_id', $partner->id)->avg('rating'), 1),
        ]);
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate(['partner_reply' => ['required', 'string', 'max:1000']]);

        $this->reviews->reply($review, $this->partner($request), $data['partner_reply']);

        return back()->with('success', __('ui.review_reply_saved'));
    }
}