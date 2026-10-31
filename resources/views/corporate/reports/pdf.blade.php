<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.reports') }} {{ $month->format('m/Y') }}</title>
    <style>
        @page { margin: 35px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111; line-height: 1.45; }
        h1 { font-size: 16px; margin: 0; color: #0a64a4; }
        h2 { font-size: 12px; margin: 16px 0 4px; color: #0a64a4; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th { background: #f2f6fa; text-align: left; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; vertical-align: top; }
        td.num, th.num { text-align: right; }
        .footer { position: fixed; bottom: -22px; left: 0; right: 0; font-size: 8px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ __('ui.reports') }}: {{ $company->company_name }}</h1>
    <div class="muted">{{ __('ui.report_month') }}: {{ $month->format('m/Y') }} | {{ $platform }}</div>

    <h2>{{ __('ui.orders_count') }}</h2>
    <table>
        <tr><td>{{ __('ui.orders_count') }}</td><td class="num">{{ $summary['orders'] }}</td></tr>
        <tr><td>{{ __('ui.total_spend') }}</td><td class="num">{{ format_price($summary['total'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.average_per_order') }}</td><td class="num">{{ format_price($summary['average'], 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.nights_total') }}</td><td class="num">{{ $summary['nights'] }}</td></tr>
        <tr><td>{{ __('ui.booking_status_cancelled') }} ({{ __('ui.refunds') }})</td>
            <td class="num">{{ $summary['cancelled'] }} ({{ format_price($summary['refunded'], 'IDR') }})</td></tr>
        <tr><td>{{ __('ui.booking_status_rejected') }}</td><td class="num">{{ $summary['rejected'] }}</td></tr>
    </table>

    @if ($summary['orders'] === 0)
        <p>{{ __('ui.no_report_data') }}</p>
    @else
        <h2>{{ __('ui.by_department') }}</h2>
        <table>
            <tr>
                <th>{{ __('ui.department') }}</th>
                <th class="num">{{ __('ui.orders_count') }}</th>
                <th class="num">{{ __('ui.total_spend') }}</th>
            </tr>
            @foreach ($byDepartment as $row)
                <tr>
                    <td>{{ $row['department'] }}</td>
                    <td class="num">{{ $row['orders'] }}</td>
                    <td class="num">{{ format_price($row['total'], 'IDR') }}</td>
                </tr>
            @endforeach
        </table>

        <h2>{{ __('ui.by_employee') }}</h2>
        <table>
            <tr>
                <th>{{ __('ui.employees') }}</th>
                <th>{{ __('ui.department') }}</th>
                <th>{{ __('ui.position') }}</th>
                <th class="num">{{ __('ui.orders_count') }}</th>
                <th class="num">{{ __('ui.total_spend') }}</th>
                <th class="num">{{ __('ui.monthly_budget') }}</th>
                <th class="num">%</th>
            </tr>
            @foreach ($byEmployee as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['position'] }}</td>
                    <td class="num">{{ $row['orders'] }}</td>
                    <td class="num">{{ format_price($row['total'], 'IDR') }}</td>
                    <td class="num">{{ $row['budget'] !== null ? format_price($row['budget'], 'IDR') : '-' }}</td>
                    <td class="num">{{ $row['used_percent'] !== null ? $row['used_percent'] . '%' : '-' }}</td>
                </tr>
            @endforeach
        </table>

        <h2>{{ __('ui.booking_detail') }}</h2>
        <table>
            <tr>
                <th>{{ __('ui.booking_code') }}</th>
                <th>{{ __('ui.booked_on') }}</th>
                <th>{{ __('ui.employees') }}</th>
                <th>{{ __('ui.hotel') }}</th>
                <th>{{ __('ui.check_in') }}</th>
                <th>{{ __('ui.check_out') }}</th>
                <th class="num">{{ __('ui.total_price') }}</th>
            </tr>
            @foreach ($rows as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td>
                    <td>{{ $booking->created_at->format('d/m/Y') }}</td>
                    <td>{{ $booking->user->companyUser?->displayName() ?? $booking->user->email }}</td>
                    <td>{{ $booking->hotel_name }} / {{ $booking->room_name }}</td>
                    <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                    <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                    <td class="num">{{ format_price($booking->total_price, 'IDR') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p class="muted">{{ __('ui.report_note') }}</p>
    <div class="footer">{{ $platform }} | {{ $company->company_name }} | {{ $month->format('m/Y') }}</div>
</body>
</html>