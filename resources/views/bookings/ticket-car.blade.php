<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.ticket_title') }} {{ $booking->booking_code }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #0f172a; line-height: 1.4; margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        .head { background: #0284c7; color: #ffffff; }
        .head td { padding: 16px 18px; vertical-align: middle; }
        .brand { font-size: 20px; font-weight: bold; }
        .subtitle { font-size: 9px; color: #e0f2fe; letter-spacing: 1px; }

        .code-box { background: #f0f9ff; margin-bottom: 4px; }
        .code-box td { padding: 12px 18px; vertical-align: middle; }
        .code { font-size: 20px; font-weight: bold; color: #0369a1; letter-spacing: 2px; }
        .status { display: inline-block; background: #d1fae5; color: #047857; font-size: 9px; font-weight: bold; padding: 5px 10px; border-radius: 10px; }

        .small { font-size: 8px; color: #64748b; }
        .right { text-align: right; }
        .muted { color: #64748b; }

        h2 { font-size: 11px; color: #0369a1; margin: 11px 0 4px; }

        .info td { padding: 2px 0; }
        .info td.label { width: 38%; color: #64748b; padding-right: 10px; }
        .info td.value { font-weight: bold; }

        .car-name { font-size: 15px; font-weight: bold; }

        .dates td { width: 50%; background: #f8fafc; padding: 9px 14px; }
        .dates td.gap { width: 8px; background: #ffffff; padding: 0; }
        .date { font-size: 15px; font-weight: bold; }

        .total { background: #f0f9ff; margin-bottom: 4px; }
        .total td { padding: 9px 14px; vertical-align: middle; }
        .amount { font-size: 17px; font-weight: bold; color: #0369a1; }

        .note { background: #f8fafc; font-size: 8px; color: #64748b; padding: 9px 14px; margin-top: 12px; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <table class="head">
        <tr>
            <td class="brand">{{ $platform }}</td>
            <td class="right subtitle">{{ __('ui.ticket_car_subtitle') }}</td>
        </tr>
    </table>

    {{-- KODE PESANAN --}}
    <table class="code-box">
        <tr>
            <td>
                <div class="small">{{ __('ui.ticket_booking_code') }}</div>
                <div class="code">{{ $booking->booking_code }}</div>
            </td>
            <td class="right">
                <span class="status">{{ __('ui.ticket_confirmed') }}</span>
            </td>
        </tr>
    </table>

    {{-- KENDARAAN --}}
    <h2>{{ __('ui.ticket_vehicle') }}</h2>
    <div class="car-name">{{ $booking->hotel_name }}</div>
    <div>{{ $booking->with_driver ? __('ui.ticket_with_driver') : __('ui.ticket_self_drive') }}</div>
    <div class="muted">{{ __('ui.ticket_pickup_location') }}: {{ $booking->pickup_location ?: '-' }}</div>

    {{-- TANGGAL SEWA --}}
    <h2>{{ __('ui.ticket_rental') }}</h2>
    <table class="dates">
        <tr>
            <td>
                <div class="small">{{ __('ui.ticket_pickup_date') }}</div>
                <div class="date">{{ $booking->check_in->format('d/m/Y') }}</div>
                <div class="muted">{{ __('ui.ticket_time') }}: {{ $booking->arrival_time ? substr($booking->arrival_time, 0, 5) : '-' }}</div>
            </td>
            <td class="gap"></td>
            <td>
                <div class="small">{{ __('ui.ticket_return_by') }}</div>
                <div class="date">{{ $booking->due_at ? $booking->due_at->format('d/m/Y') : $booking->check_out->format('d/m/Y') }}</div>
                @if ($booking->due_at)
                    <div class="muted">{{ __('ui.ticket_time') }}: {{ $booking->due_at->format('H:i') }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- DATA PENYEWA --}}
    <h2>{{ __('ui.ticket_renter') }}</h2>
    <table class="info">
        <tr><td class="label">{{ __('ui.ticket_renter_name') }}</td><td class="value">{{ $booking->guest_name }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_contact') }}</td><td class="value">{{ $booking->contact_phone ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_passengers') }}</td><td class="value">{{ $booking->guests }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_days') }}</td><td class="value">{{ $booking->nights }}</td></tr>
        @if ($booking->destination)
            <tr><td class="label">{{ __('ui.ticket_destination') }}</td><td class="value">{{ $booking->destination }}</td></tr>
        @endif
    </table>

    {{-- PERPANJANGAN --}}
    @if ($booking->extensions->isNotEmpty())
        <h2>{{ __('ui.ticket_extensions') }}</h2>
        <table class="info">
            @foreach ($booking->extensions as $extension)
                <tr>
                    <td class="label">{{ $extension->created_at->format('d/m/Y') }}</td>
                    <td>
                        {{ __('ui.ticket_extension_line', [
                            'days'   => $extension->days,
                            'date'   => $extension->new_check_out->format('d/m/Y'),
                            'amount' => format_price($extension->amount, 'IDR'),
                        ]) }}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- PEMBAYARAN --}}
    <h2>{{ __('ui.ticket_payment') }}</h2>
    <table class="total">
        <tr>
            <td class="muted">{{ __('ui.ticket_rental_paid') }}</td>
            <td class="right amount">{{ format_price($booking->total_price, 'IDR') }}</td>
        </tr>
    </table>
    <table class="info">
        {{-- Hanya tampil bila berbeda dengan total IDR --}}
        @if ($booking->displayTotal() !== format_price($booking->total_price, 'IDR'))
            <tr><td class="label">{{ __('ui.ticket_equivalent') }}</td><td class="value">{{ $booking->displayTotal() }}</td></tr>
        @endif
        <tr><td class="label">{{ __('ui.ticket_reference') }}</td><td class="value">{{ $booking->payment_reference }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_paid_at') }}</td><td class="value">{{ $booking->paid_at?->format('d/m/Y H:i') }}</td></tr>
    </table>

    {{-- BIAYA TAMBAHAN --}}
    <h2>{{ __('ui.ticket_extra_costs') }}</h2>
    <table class="info">
        <tr>
            <td class="label">{{ __('ui.ticket_paid_to_vendor') }}</td>
            <td>
                @if ($booking->with_driver && $booking->driver_fee_per_day !== null)
                    {{ __('ui.ticket_driver_fee', ['amount' => format_price($booking->driver_fee_per_day, 'IDR')]) }}<br>
                @endif
                {{ __('ui.ticket_not_included') }}
            </td>
        </tr>
        <tr>
            <td class="label">{{ __('ui.ticket_late_return') }}</td>
            <td>
                @if ($booking->late_fee_percent > 0)
                    {{ __('ui.ticket_late_policy', [
                        'minutes' => $booking->late_grace_minutes,
                        'percent' => $booking->late_fee_percent,
                    ]) }}
                @else
                    {{ __('ui.ticket_no_late_fee') }}
                @endif

                @if ($booking->returned_at && $booking->late_fee > 0)
                    <br><strong>{{ __('ui.ticket_late_recorded', ['amount' => format_price($booking->late_fee, 'IDR')]) }}</strong>
                @endif
            </td>
        </tr>
    </table>

    {{-- PEMBATALAN --}}
    <h2>{{ __('ui.ticket_cancellation') }}</h2>
    <table class="info">
        <tr>
            <td class="label">{{ __('ui.ticket_free_until') }}</td>
            <td class="value">{{ $booking->freeCancelDeadline()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="note">
        {{ __('ui.ticket_car_show_note') }}<br>
        {{ __('ui.ticket_simulation_note') }}
    </div>
</body>
</html>