<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use PartnerHelpers;

    public function edit(Request $request)
    {
        return view('partner.profile.edit', ['partner' => $this->partner($request)]);
    }

    public function update(Request $request)
    {
        $partner = $this->partner($request);

        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255'],
            'business_license_no' => ['required', 'string', 'max:100'],
            'bank_name'         => ['nullable', 'string', 'max:60', 'required_with:bank_account_no,bank_account_name'],
            'bank_account_no'   => ['nullable', 'string', 'max:50', 'regex:/^[0-9\s\-]+$/', 'required_with:bank_name,bank_account_name'],
            'bank_account_name' => ['nullable', 'string', 'max:100', 'required_with:bank_name,bank_account_no'],
        ]);

        $message = __('ui.profile_saved');

        // Data yang ditolak, setelah diperbaiki, diajukan ulang ke admin
        if ($partner->verification_status === 'rejected') {
            $data['verification_status'] = 'pending';
            $data['verification_note']   = null;
            $message = __('ui.profile_resubmitted');
        }

        $partner->update($data);

        return redirect()->route('partner.profile.edit')->with('success', $message);
    }
}