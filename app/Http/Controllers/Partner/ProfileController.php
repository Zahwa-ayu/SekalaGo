<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'proposed_commission'      => ['nullable', 'numeric', 'between:10,25'],
            'commission_proposal_note' => ['nullable', 'string', 'max:300'],
            'contact_name'        => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'address'             => ['nullable', 'string', 'max:255'],
            'city_id'             => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'website'             => ['nullable', 'url', 'max:255'],
            'business_type'       => ['nullable', Rule::in(['hotel', 'car', 'both'])],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $message = __('ui.profile_saved');

        // Data yang ditolak, setelah diperbaiki, diajukan ulang ke admin
        if ($partner->verification_status === 'rejected') {
            $data['verification_status'] = 'pending';
            $data['verification_note']   = null;
            $message = __('ui.profile_resubmitted');
        }

        $partner->update(\Illuminate\Support\Arr::except($data, ['proposed_commission', 'supporting_document']) + $partner->proposalAttributes($data));

        if ($request->hasFile('supporting_document')) {
            $old  = $partner->supporting_document_path;
            $path = $request->file('supporting_document')->store("partner-documents/{$partner->id}", 'local');

            $partner->update(['supporting_document_path' => $path]);

            if ($old) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($old);
            }
        }

        return redirect()->route('partner.profile.edit')->with('success', $message);
    }
}