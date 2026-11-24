<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews)
    {
    }

    public function index(Request $request)
    {
        $view = $request->query('view');
        abort_unless(in_array($view, [null, '', 'hidden', 'low'], true), 404);

        $q = trim((string) $request->query('q', ''));

        $list = Review::with('booking', 'partner')
            ->when($view === 'hidden', fn ($query) => $query->where('is_visible', false))
            ->when($view === 'low', fn ($query) => $query->where('rating', '<=', 2))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('comment', 'like', '%' . addcslashes($q, '%_\\') . '%')
                ->orWhere('reviewer_name', 'like', '%' . addcslashes($q, '%_\\') . '%')))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('admin.reviews.index', ['reviews' => $list, 'view' => $view, 'q' => $q]);
    }

    public function hide(Request $request, Review $review)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:300']]);

        $this->reviews->setVisibility($review, false, $request->user(), $data['reason']);

        return back()->with('success', __('ui.review_hidden_done'));
    }

    public function unhide(Request $request, Review $review)
    {
        $this->reviews->setVisibility($review, true, $request->user());

        return back()->with('success', __('ui.review_shown_done'));
    }
}