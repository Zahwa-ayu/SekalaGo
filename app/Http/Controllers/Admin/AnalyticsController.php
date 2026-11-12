<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueAnalytics;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    public function __construct(private RevenueAnalytics $analytics)
    {
    }

    public function index(Request $request)
    {
        [$filters, $start, $end] = $this->resolve($request);
        $product = $filters['product'];

        [$prevStart, $prevEnd] = $this->analytics->previous($start, $end);

        $summary  = $this->analytics->summary($start, $end, $product);
        $previous = $this->analytics->summary($prevStart, $prevEnd, $product);

        return view('admin.analytics.index', [
            'filters'   => $filters,
            'start'     => $start,
            'end'       => $end,
            'prevStart' => $prevStart,
            'prevEnd'   => $prevEnd,
            'summary'   => $summary,
            'deltas'    => $this->deltas($summary, $previous),
            'series'    => $this->analytics->series($start, $end, $product),
            'byProduct' => $this->analytics->byProduct($start, $end, $product),
            'bySegment' => $this->analytics->bySegment($start, $end, $product),
            'partners'  => $this->analytics->topPartners($start, $end, $product),
            'cities'    => $this->analytics->topCities($start, $end, $product),
            'lateFees'  => $product === 'hotel' ? null : $this->analytics->lateFees($start, $end),
        ]);
    }

    public function csv(Request $request)
    {
        [$filters, $start, $end] = $this->resolve($request);
        $product = $filters['product'];

        $filename = 'Analytics-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($start, $end, $product) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8; pemisah titik koma cocok dengan Excel berbahasa Indonesia
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                __('ui.csv_paid_on'), __('ui.booking_code'), __('ui.product'), __('ui.col_partner'),
                __('ui.city_name'), __('ui.csv_segment'), 'GMV (IDR)', __('ui.kpi_commission') . ' (IDR)', __('ui.csv_vendor') . ' (IDR)',
            ], ';', '"', '\\');

            $gmv = $fee = $vendor = 0;

            foreach ($this->analytics->detailRows($start, $end, $product) as $row) {
                fputcsv($out, [
                    substr((string) $row->paid_on, 0, 10),
                    $row->booking_code,
                    $row->product_type === 'car' ? __('ui.tab_cars') : __('ui.tab_hotels'),
                    $row->partner,
                    $row->city,
                    $row->company_id ? 'B2B' : 'B2C',
                    $row->total_price,
                    $row->platform_fee,
                    $row->vendor_revenue,
                ], ';', '"', '\\');

                $gmv    += $row->total_price;
                $fee    += $row->platform_fee;
                $vendor += $row->vendor_revenue;
            }

            fputcsv($out, ['', '', '', '', '', 'TOTAL', $gmv, $fee, $vendor], ';', '"', '\\');

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: array{range: string, from: ?string, to: ?string, product: ?string}, 1: \Illuminate\Support\Carbon, 2: \Illuminate\Support\Carbon} */
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
            [
                'range'   => $range,
                'from'    => $data['from'] ?? null,
                'to'      => $data['to'] ?? null,
                'product' => $data['product'] ?? null,
            ],
            $start,
            $end,
        ];
    }

    /** Perubahan persen dibanding periode sebelumnya; null jika pembandingnya nol. */
    private function deltas(array $now, array $before): array
    {
        return collect(['gmv', 'fee', 'orders', 'average', 'refunded'])
            ->mapWithKeys(fn ($key) => [
                $key => $before[$key] > 0 ? round(($now[$key] - $before[$key]) / $before[$key] * 100, 1) : null,
            ])
            ->all();
    }
}