<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.partner_reports') }} {{ $partner->company_name }}</title>
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
    </style>
</head>
<body>
    <h1>{{ __('ui.partner_reports') }}: {{ $partner->company_name }}</h1>
    <div class="muted">{{ __('ui.audit_period') }}: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }} | {{ $platform }} | {{ now()->format('d/m/Y H:i') }}</div>

    <h2>{{ __('ui.preport_title') }}</h2>
    <table>
        <tr><td>{{ __('ui.preport_earnings') }}</td><td class="num">{{ format_price($summary['earnings'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_cancel_fees') }}</td><td class="num">{{ format_price($summary['cancel_fee_share'], 'IDR') }}</td></tr>
        <tr><th>{{ __('ui.preport_income_total') }}</th><th class="num">{{ format_price($summary['income'], 'IDR') }}</th></tr>
        <tr><td>{{ __('ui.preport_gmv') }}</td><td class="num">{{ format_price($summary['gmv'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_commission') }}</td><td class="num">{{ format_price($summary['commission'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_orders') }}</td><td class="num">{{ $summary['orders'] }}</td></tr>
        <tr><td>{{ __('ui.preport_units') }}</td><td class="num">{{ $summary['units'] }}</td></tr>
        <tr><td>{{ __('ui.preport_late_fees', ['amount' => '', 'count' => $summary['late_fee_count']]) }}</td><td class="num">{{ format_price($summary['late_fees'], 'IDR') }}</td></tr>
    </table>

    <h2>{{ __('ui.preport_payouts_title') }}</h2>
    <table>
        <tr><td>{{ __('ui.preport_ready') }}</td><td class="num">{{ format_price($payouts['ready'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_waiting') }}</td><td class="num">{{ format_price($payouts['waiting'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_pending') }}</td><td class="num">{{ format_price($payouts['pending'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.preport_transferred') }}</td><td class="num">{{ format_price($payouts['transferred'], 'IDR') }}</td></tr>
    </table>

    <h2>{{ __('ui.preport_by_item') }}</h2>
    <table>
        <tr><th>{{ __('ui.preport_col_item') }}</th><th class="num">{{ __('ui.col_orders') }}</th><th class="num">{{ __('ui.preport_col_units') }}</th><th class="num">GMV</th><th class="num">{{ __('ui.preport_earnings') }}</th></tr>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->name }}</td><td class="num">{{ $item->orders }}</td><td class="num">{{ $item->units }}</td>
                <td class="num">{{ format_price($item->gmv, 'IDR') }}</td><td class="num">{{ format_price($item->earnings, 'IDR') }}</td>
            </tr>
        @endforeach
    </table>

    @if ($penalties->isNotEmpty())
        <h2>{{ __('ui.cancel_fee_amount') }}</h2>
        <table>
            <tr><th>{{ __('ui.booking_code') }}</th><th>{{ __('ui.item_name') }}</th><th>{{ __('ui.cancel_fee_report_cancelled_on') }}</th><th class="num">{{ __('ui.cancel_fee_amount') }}</th><th class="num">{{ __('ui.preport_cancel_fees') }}</th></tr>
            @foreach ($penalties as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td><td>{{ $booking->hotel_name }}</td><td>{{ $booking->cancelled_at?->format('d/m/Y') }}</td>
                    <td class="num">{{ format_price($booking->cancel_fee, 'IDR') }}</td><td class="num">{{ format_price($booking->vendor_revenue, 'IDR') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p class="muted">{{ __('ui.preport_note_date') }}</p>
</body>
</html>