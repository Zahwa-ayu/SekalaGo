<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.financial_audit') }} {{ $company->company_name }}</title>
    <style>
        @page { margin: 30px 35px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111; line-height: 1.4; }
        h1 { font-size: 15px; margin: 0; color: #0a64a4; }
        h2 { font-size: 11px; margin: 14px 0 4px; color: #0a64a4; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th { background: #f2f6fa; text-align: left; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; vertical-align: top; }
        .num { text-align: right; }
        .pass { color: #0a7a2f; } .fail { color: #b91c1c; font-weight: bold; } .warn { color: #b45309; font-weight: bold; } .info { color: #0369a1; }
    </style>
</head>
<body>
    <h1>{{ __('ui.financial_audit') }}: {{ $company->company_name }}</h1>
    <div class="muted">{{ __('ui.audit_period') }}: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }} | {{ $platform }} | {{ now()->format('d/m/Y H:i') }}</div>
    <p><strong>{{ __('ui.audit_verdict_' . $verdict) }}</strong></p>

    <h2>{{ __('ui.audit_statement') }}</h2>
    <table>
        <tr><td>{{ __('ui.audit_opening') }}</td><td class="num">{{ format_price($statement['opening'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.audit_topups') }}</td><td class="num">{{ format_price($statement['topups'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.audit_charges') }}</td><td class="num">{{ format_price($statement['charges'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.audit_refunds') }}</td><td class="num">{{ format_price($statement['refunds'], 'IDR') }}</td></tr>
        <tr><th>{{ __('ui.audit_closing') }}</th><th class="num">{{ format_price($statement['closing'], 'IDR') }}</th></tr>
    </table>

    <h2>{{ __('ui.audit_checks') }}</h2>
    <table>
        @foreach ($checks as $check)
            <tr>
                <td style="width: 38%">{{ __('ui.audit_check_' . $check['key']) }}</td>
                <td class="{{ $check['status'] }}" style="width: 12%">
                    {{ $check['status'] === 'pass' ? __('ui.audit_pass') : __('ui.audit_items', ['count' => $check['items']->count()]) }}
                </td>
                <td>
                    @foreach ($check['items']->take(8) as $item)
                        {{ $item['label'] }}: {{ $item['detail'] }}<br>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </table>

    <h2>{{ __('ui.audit_ledger') }}</h2>
    <table>
        <tr>
            <th>{{ __('ui.audit_col_date') }}</th><th>{{ __('ui.audit_col_type') }}</th><th>{{ __('ui.audit_col_reference') }}</th>
            <th>{{ __('ui.audit_col_note') }}</th><th class="num">{{ __('ui.audit_col_amount') }}</th><th class="num">{{ __('ui.audit_col_balance') }}</th><th>{{ __('ui.audit_col_by') }}</th>
        </tr>
        @foreach ($statement['rows'] as $tx)
            <tr>
                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ __('ui.deposit_type_' . $tx->type) }}</td>
                <td>{{ $tx->reference ?: '-' }}</td>
                <td>{{ $tx->note ?: '-' }}</td>
                <td class="num">{{ format_price($tx->amount, 'IDR') }}</td>
                <td class="num">{{ format_price($tx->balance_after, 'IDR') }}</td>
                <td>{{ $tx->creator?->email ?? '-' }}</td>
            </tr>
        @endforeach
    </table>

    <p class="muted">{{ __('ui.audit_immutable_note') }}</p>
</body>
</html>