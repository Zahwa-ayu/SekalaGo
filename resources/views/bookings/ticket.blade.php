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

        h2 { font-size: 11px; color: #0369a1; margin: 13px 0 4px; }

        .info td { padding: 3px 0; }
        .info td.label { width: 38%; color: #64748b; padding-right: 10px; }
        .info td.value { font-weight: bold; }

        .hotel-name { font-size: 15px; font-weight: bold; }
        .muted { color: #64748b; }

        .dates td { width: 50%; background: #f8fafc; padding: 10px 14px; }
        .dates td.gap { width: 8px; background: #ffffff; padding: 0; }
        .date { font-size: 15px; font-weight: bold; }

        .total { background: #f0f9ff; margin-bottom: 4px; }
        .total td { padding: 10px 14px; vertical-align: middle; }
        .amount { font-size: 17px; font-weight: bold; color: #0369a1; }

        .note { background: #f8fafc; font-size: 8px; color: #64748b; padding: 10px 14px; margin-top: 14px; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <table class="head">
        <tr>
            <td class="brand">{{ $platform }}</td>
            <td class="right subtitle">{{ __('ui.ticket_subtitle') }}</td>
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

    {{-- HOTEL --}}
    <h2>{{ __('ui.ticket_hotel') }}</h2>
    <div class="hotel-name">{{ $booking->hotel_name }}</div>
    <div>{{ $booking->room_name }}</div>
    <div class="muted">
        {{ $booking->hotel?->city ?? '-' }}@if ($booking->hotel?->address), {{ $booking->hotel->address }}@endif
    </div>

    {{-- TANGGAL MENGINAP --}}
    <h2>{{ __('ui.ticket_stay') }}</h2>
    <table class="dates">
        <tr>
            <td>
                <div class="small">{{ __('ui.ticket_check_in') }}</div>
                <div class="date">{{ $booking->check_in->format('d/m/Y') }}</div>
                <div class="muted">{{ __('ui.ticket_time') }}: {{ $booking->hotel ? $booking->hotel->checkInLabel() : '-' }}</div>
            </td>
            <td class="gap"></td>
            <td>
                <div class="small">{{ __('ui.ticket_check_out') }}</div>
                <div class="date">{{ $booking->check_out->format('d/m/Y') }}</div>
                <div class="muted">{{ __('ui.ticket_time') }}: {{ $booking->hotel ? $booking->hotel->checkOutLabel() : '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- DATA TAMU --}}
    <h2>{{ __('ui.ticket_guest') }}</h2>
    <table class="info">
        <tr><td class="label">{{ __('ui.ticket_guest_name') }}</td><td class="value">{{ $booking->guest_name }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_guest_count') }}</td><td class="value">{{ $booking->guests }}</td></tr>
        <tr><td class="label">{{ __('ui.ticket_nights') }}</td><td class="value">{{ $booking->nights }}</td></tr>
        @if ($booking->arrival_time)
            <tr><td class="label">{{ __('ui.ticket_arrival') }}</td><td class="value">{{ substr($booking->arrival_time, 0, 5) }}</td></tr>
        @endif
        @if ($booking->special_requests)
            <tr><td class="label">{{ __('ui.ticket_requests') }}</td><td class="value">{{ $booking->special_requests }}</td></tr>
        @endif
    </table>

    {{-- PEMBAYARAN --}}
    <h2>{{ __('ui.ticket_payment') }}</h2>
    <table class="total">
        <tr>
            <td class="muted">{{ __('ui.ticket_total_paid') }}</td>
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

    {{-- PEMBATALAN --}}
    <h2>{{ __('ui.ticket_cancellation') }}</h2>
    <table class="info">
        <tr>
            <td class="label">{{ __('ui.ticket_free_until') }}</td>
            <td class="value">{{ $booking->freeCancelDeadline()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('ui.ticket_after_free') }}</td>
            <td>{{ __('ui.ticket_fee_policy', ['percent' => $booking->late_cancel_fee_percent]) }}</td>
        </tr>
    </table>

    <div class="note">
        {{ __('ui.ticket_show_note') }}<br>
        {{ __('ui.ticket_simulation_note') }}
    </div>
</body>
</html>