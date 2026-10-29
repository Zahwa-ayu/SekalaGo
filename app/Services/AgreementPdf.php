<?php

namespace App\Services;

use App\Models\PartnerAgreement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AgreementPdf
{
    /** Isi PDF (biner). */
    public function binary(PartnerAgreement $agreement): string
    {
        $agreement->loadMissing('partner');

        return Pdf::loadView('agreements.pdf', [
            'agreement' => $agreement,
            'partner'   => $agreement->partner,
            'platform'  => config('app.name'),
        ])->setPaper('a4')->output();
    }

    /** Simpan PDF final saat MOU ditandatangani. */
    public function storeSigned(PartnerAgreement $agreement): void
    {
        $path = 'agreements/' . $agreement->documentNumber() . '.pdf';

        Storage::disk('local')->put($path, $this->binary($agreement));

        $agreement->update(['pdf_path' => $path]);
    }

    /** Respons unduhan: file tersimpan jika sudah ditandatangani, kalau belum dibuat saat itu juga (DRAFT). */
    public function download(PartnerAgreement $agreement): Response
    {
        $filename = $agreement->documentNumber() . ($agreement->status === 'signed' ? '' : '-DRAFT') . '.pdf';

        if ($agreement->status === 'signed'
            && $agreement->pdf_path
            && Storage::disk('local')->exists($agreement->pdf_path)) {
            return Storage::disk('local')->download($agreement->pdf_path, $filename);
        }

        return response($this->binary($agreement), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}