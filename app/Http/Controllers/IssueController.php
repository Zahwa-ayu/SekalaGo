<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\IssueReport;
use App\Services\IssueService;
use App\Support\AdminAbilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{
    public function __construct(private IssueService $issues)
    {
    }

    /** Daftar laporan milik pengguna sendiri. */
    public function index(Request $request)
    {
        return view('issues.index', [
            'reports' => IssueReport::with('booking')->where('user_id', $request->user()->id)->latest()
                ->paginate((int) config('sekalago.per_page', 15)),
        ]);
    }

    public function create(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $categories = $this->issues->allowedCategories($booking, auth()->user());

        if ($categories === []) {
            return redirect()->route('bookings.show', $booking)->with('error', __('ui.issue_not_allowed'));
        }

        return view('issues.create', compact('booking', 'categories'));
    }

    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'category'    => ['required', 'string', 'in:' . implode(',', IssueService::CATEGORIES)],
            'description' => ['required', 'string', 'min:15', 'max:2000'],
            'evidence'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $report = $this->issues->create($booking, $request->user(), $data['category'], $data['description'], $request->file('evidence'));

        return redirect()->route('issues.index')->with('success', __('ui.issue_submitted', ['code' => $report->code]));
    }

    /** Bukti: hanya pelapor, mitra yang dilaporkan, dan admin Ops. */
    public function evidence(Request $request, IssueReport $issue)
    {
        $user    = $request->user();
        $allowed = $issue->user_id === $user->id
            || ($user->user_type === 'partner' && $user->partner?->id === $issue->partner_id)
            || ($user->user_type === 'admin' && AdminAbilities::can($user, 'operations'));

        abort_unless($allowed && $issue->evidence_path && Storage::disk('local')->exists($issue->evidence_path), 404);

        return Storage::disk('local')->response($issue->evidence_path);
    }
}