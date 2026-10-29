<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\AgreementPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    private const STATUSES = ['pending', 'verified', 'rejected', 'all'];

    private const PAYOUT_LABELS = [
        'weekly'   => 'setiap minggu',
        'biweekly' => 'setiap dua minggu',
        'monthly'  => 'setiap bulan',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $partners = Partner::with(['user', 'latestAgreement'])
            ->when($status !== 'all', fn ($q) => $q->where('verification_status', $status))
            ->latest()
            ->get();

        $counts = Partner::selectRaw('verification_status, count(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return view('admin.partners.index', compact('partners', 'status', 'counts'));
    }

    public function show(Partner $partner)
    {
        $partner->load('user', 'latestAgreement')->loadCount('hotels');

        return view('admin.partners.show', [
            'partner'   => $partner,
            'agreement' => $partner->latestAgreement,
        ]);
    }

    public function downloadAgreement(Partner $partner, AgreementPdf $pdf)
    {
        $agreement = $partner->latestAgreement;
        abort_unless($agreement, 404);

        return $pdf->download($agreement);
    }

    /** Verifikasi data mitra sekaligus mengirim (atau memperbarui) MOU. */
    public function sendAgreement(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'commission_percent' => ['required', 'numeric', 'between:10,25'],
            'payout_schedule'    => ['required', Rule::in(array_keys(self::PAYOUT_LABELS))],
            'free_cancel_days'   => ['required', 'integer', 'between:0,30'],
        ]);

        if ($partner->agreements()->where('status', 'signed')->exists()) {
            return back()->withErrors(['agreement' => __('ui.mou_already_signed')]);
        }

        DB::transaction(function () use ($partner, $data) {
            // MOU lama yang belum ditandatangani diganti versi baru
            $partner->agreements()->where('status', 'sent')->update(['status' => 'void']);

            $version = ((int) $partner->agreements()->max('version')) + 1;
            $rate    = round($data['commission_percent'] / 100, 4);

            $content = view('agreements.standard', [
                'platform'       => config('app.name'),
                'number'         => 'MOU-' . str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT) . '-v' . $version,
                'date'           => now()->format('d/m/Y'),
                'company'        => $partner->company_name,
                'license'        => $partner->business_license_no,
                'commission'     => rtrim(rtrim(number_format($rate * 100, 2, '.', ''), '0'), '.'),
                'exampleGross'   => number_format(500000, 0, ',', '.'),
                'exampleFee'     => number_format(500000 * $rate, 0, ',', '.'),
                'exampleNet'     => number_format(500000 - 500000 * $rate, 0, ',', '.'),
                'payout'         => self::PAYOUT_LABELS[$data['payout_schedule']],
                'freeCancelDays' => $data['free_cancel_days'],
            ])->render();

            $partner->agreements()->create([
                'version'          => $version,
                'commission_rate'  => $rate,
                'payout_schedule'  => $data['payout_schedule'],
                'free_cancel_days' => $data['free_cancel_days'],
                'content'          => $content,
                'status'           => 'sent',
                'sent_at'          => now(),
            ]);

            $partner->update(['verification_status' => 'verified', 'verification_note' => null]);
        });

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.mou_sent'));
    }

    public function reject(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'verification_note' => ['required', 'string', 'max:500'],
        ]);

        $partner->update([
            'verification_status' => 'rejected',
            'verification_note'   => $data['verification_note'],
        ]);

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_rejected_msg'));
    }

    /** Buka kembali pengajuan yang ditolak (misalnya setelah mitra dihubungi dan melengkapi data). */
    public function reopen(Partner $partner)
    {
        $partner->update(['verification_status' => 'pending', 'verification_note' => null]);

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_reopened'));
    }
}