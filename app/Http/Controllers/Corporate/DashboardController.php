<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use CorporateHelpers;

    public function index(Request $request)
    {
        $member  = $this->companyUser($request);
        $company = $member->company;

        $data = [
            'member'  => $member,
            'company' => $company,
            'limit'   => $member->bookingLimit(),
        ];

        if ($member->isFinance()) {
            $data['employeeCount']    = $company->companyUsers()->count();
            $data['transactions']     = $company->transactions()->latest()->limit(10)->get();
            $data['pendingApprovals'] = Booking::where('company_id', $company->id)
                ->where('status', 'pending_approval')
                ->count();
        }

        return view('corporate.dashboard', $data);
    }
}