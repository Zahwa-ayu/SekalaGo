<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\AgreementPdf;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    use PartnerHelpers;

    public function __construct(private AgreementPdf $pdf)
    {
    }

    public function show(Request $request)
    {
        $partner = $this->partner($request);

        return view('partner.agreement.show', [
            'partner'   => $partner,
            'agreement' => $partner->latestAgreement,
        ]);
    }

    public function download(Request $request)
    {
        $agreement = $this->partner($request)->latestAgreement;
        abort_unless($agreement, 404);

        return $this->pdf->download($agreement);
    }

    public function sign(Request $request)
    {
        $partner   = $this->partner($request);
        $agreement = $partner->latestAgreement;

        if (! $agreement || $agreement->status !== 'sent') {
            return redirect()->route('partner.agreement.show');
        }

        $data = $request->validate([
            'agree'       => ['accepted'],
            'signed_name' => ['required', 'string', 'max:255'],
        ]);

        $agreement->update([
            'status'      => 'signed',
            'signed_at'   => now(),
            'signed_name' => $data['signed_name'],
            'signed_ip'   => $request->ip(),
        ]);

        // Sidik jari dihitung dari data yang sudah tersimpan, lalu PDF final dibuat dan disimpan
        $agreement->refresh();
        $agreement->update(['signature_hash' => $agreement->expectedHash()]);
        $this->pdf->storeSigned($agreement);

        return redirect()->route('partner.agreement.show')->with('success', __('ui.mou_signed'));
    }
}