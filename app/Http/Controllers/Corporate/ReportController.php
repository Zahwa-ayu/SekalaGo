<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Services\CompanyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    use CorporateHelpers;

    public function __construct(private CompanyReport $reports)
    {
    }

    public function index(Request $request)
    {
        [$company, $month] = $this->resolve($request);

        $months = collect(range(0, 11))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        return view('corporate.reports.index', $this->reports->build($company, $month) + ['months' => $months]);
    }

    public function csv(Request $request)
    {
        [$company, $month] = $this->resolve($request);
        $report = $this->reports->build($company, $month);

        $filename = 'Laporan-' . Str::slug($company->company_name) . '-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8; pemisah titik koma cocok dengan Excel berbahasa Indonesia
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                __('ui.booking_code'), __('ui.booked_on'), __('ui.employees'), __('ui.department'),
                __('ui.hotel'), __('ui.room_name'), __('ui.check_in'), __('ui.check_out'),
                __('ui.nights'), __('ui.total_price') . ' (IDR)', __('ui.limit_label') . ' (IDR)',
            ], ';', '"', '\\');

            foreach ($report['rows'] as $booking) {
                fputcsv($out, [
                    $booking->booking_code,
                    $booking->created_at->format('Y-m-d'),
                    $booking->user->companyUser?->displayName() ?? $booking->user->email,
                    $booking->department ?? '-',
                    $booking->hotel_name,
                    $booking->room_name,
                    $booking->check_in->format('Y-m-d'),
                    $booking->check_out->format('Y-m-d'),
                    $booking->nights,
                    $booking->total_price,
                    $booking->policy_limit ?? '',
                ], ';', '"', '\\');
            }

            fputcsv($out, ['', '', '', '', '', '', '', '', 'TOTAL', $report['summary']['total'], ''], ';', '"', '\\');

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        [$company, $month] = $this->resolve($request);

        // Font PDF tidak memuat huruf Hangul, jadi dokumen selalu dibuat dalam bahasa Indonesia
        app()->setLocale('id');

        $report = $this->reports->build($company, $month);

        return Pdf::loadView('corporate.reports.pdf', $report + ['platform' => config('app.name')])
            ->setPaper('a4', 'landscape')
            ->download('Laporan-' . Str::slug($company->company_name) . '-' . $month->format('Y-m') . '.pdf');
    }

    /** @return array{0: \App\Models\Company, 1: Carbon} */
    private function resolve(Request $request): array
    {
        $company = $this->company($request);

        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        // Tanggal 1 sengaja ditulis eksplisit supaya bulan pendek tidak "meluap" ke bulan berikutnya
        $month = isset($data['month'])
            ? Carbon::parse($data['month'] . '-01')->startOfMonth()
            : now()->startOfMonth();

        return [$company, $month];
    }
}