<?php

namespace App\Http\Controllers\Corporate;

use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\Request;

trait CorporateHelpers
{
    protected function companyUser(Request $request): CompanyUser
    {
        $member = $request->user()->companyUser;
        abort_unless($member, 403);

        return $member->loadMissing('company', 'position');
    }

    protected function company(Request $request): Company
    {
        return $this->companyUser($request)->company;
    }
}