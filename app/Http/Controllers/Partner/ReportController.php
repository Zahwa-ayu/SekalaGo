<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerReport;
use App\Services\RevenueAnalytics;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    use PartnerHelpers;

    public function __construct(private PartnerReport $reports, private RevenueAnalytics $analytics)
    {
    }

    public function index(Request $request)
    {
        [$filters, $start, $end] = $this->resolve($request);

        $data = $this->reports->build($this->partner($request), $start, $end, $filters['product']);

        return view('partner.reports.index', $data + compact('filters', 'start', 'end'));
    }

    public function csv(Request $request)
    {
        [$filters, $start, $end] = $this->resolve($request);

        $partner  = $this->partner($request);
        $product  = $filters['product'];
        $summary  = $this->reports->build($partner, $start, $end, $product)['summary'];
        $filename = 'Laporan-' . Str::slug($partner->company_name) . '-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($partner, $start, $end, $product, $summary) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8

            $put = fn (array $row) => fputcsv($out, $row, ';', '"', '\\');

            $put([$partner->company_name]);
            $put([__('ui.audit_period'), $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y')]);
            $put([]);
            $put([
                __('ui.csv_paid_on'), __('ui.booking_code'), __('ui.product'), __('ui.item_name'), __('ui.guest_name'),
                __('ui.period_start'), __('ui.period_end'), __('ui.preport_col_units'),
                'GMV (IDR)', __('ui.kpi_commission') . ' (IDR)', __('ui.preport_earnings') . ' (IDR)', __('ui.payout_code'),
            ]);

            $gmv = $fee = $earnings = 0;

            foreach ($this->reports->detailRows($partner, $start, $end, $product) as $row) {
                $put([
                    substr((string) $row->paid_on, 0, 10),
                    $row->booking_code,
                    $row->product_type === 'car' ? __('ui.tab_cars') : __('ui.tab_hotels'),
                    $row->hotel_name . ' / ' . $row->room_name,
                    $row->guest_name,
                    substr((string) $row->check_in, 0, 10),
                    substr((string) $row->check_out, 0, 10),
                    $row->nights,
                    $row->total_price,
                    $row->platform_fee,
                    $row->vendor_revenue,
                    $row->payout_code ? $row->payout_code . ' (' . __('ui.payout_status_' . $row->payout_status) . ')' : '-',
                ]);

                $gmv      += $row->total_price;
                $fee      += $row->platform_fee;
                $earnings += $row->vendor_revenue;
            }

            $put(['', '', '', '', '', '', '', 'TOTAL', $gmv, $fee, $earnings, '']);

            $penalties = $this->reports->penaltyRows($partner, $start, $end, $product);

            if ($penalties->isNotEmpty()) {
                $put([]);
                $put([__('ui.cancel_fee_amount')]);

                foreach ($penalties as $booking) {
                    $put([$booking->booking_code, $booking->hotel_name, $booking->cancelled_at?->format('Y-m-d'), $booking->cancel_fee, $booking->vendor_revenue]);
                }

                $put(['TOTAL', '', '', $penalties->sum('cancel_fee'), $penalties->sum('vendor_revenue')]);
            }

            $put([]);
            $put([__('ui.preport_income_total'), $summary['income']]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        [$filters, $start, $end] = $this->resolve($request);

        // Font PDF tidak memuat huruf Hangul, jadi dokumen selalu dibuat dalam bahasa Indonesia
        app()->setLocale('id');

        $partner = $this->partner($request);
        $data    = $this->reports->build($partner, $start, $end, $filters['product']);

        return Pdf::loadView('partner.reports.pdf', $data + [
            'partner'   => $partner,
            'start'     => $start,
            'end'       => $end,
            'penalties' => $this->reports->penaltyRows($partner, $start, $end, $filters['product']),
            'platform'  => config('app.name'),
        ])->setPaper('a4', 'landscape')
          ->download('Laporan-' . Str::slug($partner->company_name) . '-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf');
    }

    /** @return array{0: array<string, ?string>, 1: \Illuminate\Support\Carbon, 2: \Illuminate\Support\Carbon} */
    private function resolve(Request $request): array
    {
        $data = $request->validate([
            'range'   => ['nullable', Rule::in(RevenueAnalytics::PRESETS)],
            'from'    => ['nullable', 'date', 'required_if:range,custom'],
            'to'      => ['nullable', 'date', 'after_or_equal:from', 'required_if:range,custom'],
            'product' => ['nullable', Rule::in(['hotel', 'car'])],
        ]);

        $range = $data['range'] ?? 'this_month';

        [$start, $end] = $this->analytics->period($range, $data['from'] ?? null, $data['to'] ?? null);

        if ($this->analytics->daysIn($start, $end) > 366) {
            throw ValidationException::withMessages(['to' => __('ui.analytics_range_too_long')]);
        }

        return [
            ['range' => $range, 'from' => $data['from'] ?? null, 'to' => $data['to'] ?? null, 'product' => $data['product'] ?? null],
            $start,
            $end,
        ];
    }
}