<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Services\IssueService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    public function __construct(private IssueService $issues)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        abort_unless(in_array($status, ['active', 'open', 'in_review', 'resolved', 'rejected', 'all'], true), 404);

        $reports = IssueReport::with('booking', 'user', 'partner', 'handler')
            ->search($request->query('q'), ['code', 'description', 'booking.booking_code', 'user.email'])
            ->when($status === 'active', fn ($q) => $q->whereIn('status', ['open', 'in_review']))
            ->when(! in_array($status, ['active', 'all'], true), fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'open' DESC")
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('admin.issues.index', [
            'reports' => $reports,
            'status'  => $status,
            'counts'  => IssueReport::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, IssueReport $issue)
    {
        $data = $request->validate([
            'status'     => ['required', Rule::in(['in_review', 'resolved', 'rejected'])],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->issues->handle($issue, $request->user(), $data['status'], $data['resolution'] ?? null);

        return back()->with('success', __('ui.issue_updated_done'));
    }
}