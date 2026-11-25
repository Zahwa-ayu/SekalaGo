<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Services\IssueService;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    use PartnerHelpers;

    public function __construct(private IssueService $issues)
    {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);

        $view     = (string) $request->query('view', 'active');
        $category = (string) $request->query('category', '');

        abort_unless(in_array($view, ['active', 'needs_response', 'resolved', 'rejected', 'all'], true), 404);
        abort_unless($category === '' || in_array($category, IssueService::CATEGORIES, true), 404);

        $base   = IssueReport::where('partner_id', $partner->id);
        $active = fn ($q) => $q->whereIn('status', ['open', 'in_review']);

        $reports = (clone $base)->with('booking')
            ->search($request->query('q'), ['code', 'description', 'booking.booking_code'])
            ->when($view === 'active', $active)
            ->when($view === 'needs_response', fn ($q) => $active($q)->whereNull('partner_response'))
            ->when(in_array($view, ['resolved', 'rejected'], true), fn ($q) => $q->where('status', $view))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('partner.issues.index', [
            'reports'  => $reports,
            'view'     => $view,
            'category' => $category,
            'counts'   => [
                'active'         => $active(clone $base)->count(),
                'needs_response' => $active(clone $base)->whereNull('partner_response')->count(),
                'resolved'       => (clone $base)->where('status', 'resolved')->count(),
                'rejected'       => (clone $base)->where('status', 'rejected')->count(),
                'all'            => (clone $base)->count(),
            ],
        ]);
    }

    public function respond(Request $request, IssueReport $issue)
    {
        $data = $request->validate(['partner_response' => ['required', 'string', 'max:1000']]);

        $this->issues->respond($issue, $this->partner($request)->id, $data['partner_response']);

        return back()->with('success', __('ui.issue_response_saved'));
    }
}