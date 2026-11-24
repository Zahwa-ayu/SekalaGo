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
        return view('partner.issues.index', [
            'reports' => IssueReport::with('booking')->where('partner_id', $this->partner($request)->id)->latest()
                ->paginate((int) config('sekalago.per_page', 15)),
        ]);
    }

    public function respond(Request $request, IssueReport $issue)
    {
        $data = $request->validate(['partner_response' => ['required', 'string', 'max:1000']]);

        $this->issues->respond($issue, $this->partner($request)->id, $data['partner_response']);

        return back()->with('success', __('ui.issue_response_saved'));
    }
}