<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CorporateAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuditController extends Controller
{
    use CorporateHelpers;

    public function __construct(private CorporateAudit $audit)
    {
    }

    public function index(Request $request, ?Company $company = null)
    {
        [$company, $routes] = $this->context($request, $company);
        [$filters, $start, $end] = $this->period($request);

        $checks = $this->audit->checks($company);

        return view('corporate.audit.index', [
            'company'   => $company,
            'routes'    => $routes,
            'filters'   => $filters,
            'start'     => $start,
            'end'       => $end,
            'statement' => $this->audit->statement($company, $start, $end),
            'checks'    => $checks,
            'verdict'   => $this->audit->verdict($checks),
        ]);
    }

    public function csv(Request $request, ?Company $company = null)
    {
        [$company] = $this->context($request, $company);
        [, $start, $end] = $this->period($request);

        $statement = $this->audit->statement($company, $start, $end);
        $filename  = 'Mutasi-saldo-' . Str::slug($company->company_name) . '-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($company, $start, $end, $statement) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8

            $put = fn (array $row) => fputcsv($out, $row, ';', '"', '\\');

            $put([$company->company_name]);
            $put([__('ui.audit_period'), $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y')]);
            $put([__('ui.audit_opening'), $statement['opening']]);
            $put([]);
            $put([__('ui.audit_col_date'), __('ui.audit_col_type'), __('ui.audit_col_reference'), __('ui.audit_col_note'), __('ui.audit_col_amount') . ' (IDR)', __('ui.audit_col_balance') . ' (IDR)', __('ui.audit_col_by')]);

            foreach ($statement['rows'] as $tx) {
                $put([
                    $tx->created_at->format('Y-m-d H:i'), __('ui.deposit_type_' . $tx->type), $tx->reference, $tx->note,
                    $tx->amount, $tx->balance_after, $tx->creator?->email,
                ]);
            }

            $put([]);
            $put([__('ui.audit_topups'), $statement['topups']]);
            $put([__('ui.audit_charges'), $statement['charges']]);
            $put([__('ui.audit_refunds'), $statement['refunds']]);
            $put([__('ui.audit_closing'), $statement['closing']]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request, ?Company $company = null)
    {
        [$company] = $this->context($request, $company);
        [, $start, $end] = $this->period($request);

        // Font PDF tidak memuat huruf Hangul, jadi dokumen selalu dibuat dalam bahasa Indonesia
        app()->setLocale('id');

        $checks = $this->audit->checks($company);

        return Pdf::loadView('corporate.audit.pdf', [
            'company'   => $company,
            'start'     => $start,
            'end'       => $end,
            'statement' => $this->audit->statement($company, $start, $end),
            'checks'    => $checks,
            'verdict'   => $this->audit->verdict($checks),
            'platform'  => config('app.name'),
        ])->setPaper('a4', 'landscape')
          ->download('Audit-' . Str::slug($company->company_name) . '-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf');
    }

    /** Perusahaan yang diaudit dan nama rute untuk tautan di tampilan. */
    private function context(Request $request, ?Company $company): array
    {
        if ($request->routeIs('admin.*')) {
            abort_unless($company, 404);

            return [$company, [
                'index'  => 'admin.companies.audit',
                'csv'    => 'admin.companies.audit.csv',
                'pdf'    => 'admin.companies.audit.pdf',
                'params' => ['company' => $company->id],
            ]];
        }

        return [$this->company($request), [
            'index'  => 'corporate.audit.index',
            'csv'    => 'corporate.audit.csv',
            'pdf'    => 'corporate.audit.pdf',
            'params' => [],
        ]];
    }

    /** @return array{0: array<string, ?string>, 1: Carbon, 2: Carbon} */
    private function period(Request $request): array
    {
        $data = $request->validate([
            'range' => ['nullable', Rule::in(['this_month', 'last_month', 'this_quarter', 'this_year', 'custom'])],
            'from'  => ['nullable', 'date', 'required_if:range,custom'],
            'to'    => ['nullable', 'date', 'after_or_equal:from', 'required_if:range,custom'],
        ]);

        $range = $data['range'] ?? 'this_month';
        $today = today();

        [$start, $end] = match ($range) {
            'last_month'   => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_quarter' => [$today->copy()->firstOfQuarter()->startOfDay(), $today->copy()->endOfDay()],
            'this_year'    => [$today->copy()->startOfYear(), $today->copy()->endOfDay()],
            'custom'       => [Carbon::parse($data['from'])->startOfDay(), Carbon::parse($data['to'])->endOfDay()],
            default        => [$today->copy()->startOfMonth(), $today->copy()->endOfDay()],
        };

        if ($start->diffInDays($end) > 800) {
            throw ValidationException::withMessages(['to' => __('ui.audit_range_too_long')]);
        }

        return [['range' => $range, 'from' => $data['from'] ?? null, 'to' => $data['to'] ?? null], $start, $end];
    }
}