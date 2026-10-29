@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.incoming_bookings') }}</h1>

    <table border="1" cellpadding="6">
        <tr><td>{{ __('ui.orders_count') }}</td><td>{{ $summary->orders }}</td></tr>
        <tr><td>{{ __('ui.gross_sales') }}</td><td>{{ format_price($summary->gross, 'IDR') }}</td></tr>
        <tr><td>{{ __('ui.platform_commission') }}</td><td>{{ format_price($summary->fee, 'IDR') }}</td></tr>
        <tr>
            <td><strong>{{ __('ui.net_revenue') }}</strong></td>
            <td><strong>{{ format_price($summary->net, 'IDR') }}</strong></td>
        </tr>
    </table>
    <p><small>{{ __('ui.revenue_note') }}</small></p>

    <p>
        @foreach (['confirmed', 'pending_payment', 'cancelled', 'expired'] as $s)
            <a href="{{ route('partner.bookings.index', ['status' => $s]) }}">
                {{ $status === $s ? '▶ ' : '' }}{{ __('ui.booking_status_' . $s) }}
            </a> |
        @endforeach
        <a href="{{ route('partner.bookings.index', ['status' => 'all']) }}">
            {{ $status === 'all' ? '▶ ' : '' }}{{ __('ui.all') }}
        </a>
    </p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.booking_code') }}</th>
                <th>{{ __('ui.hotel') }} / {{ __('ui.room_name') }}</th>
                <th>{{ __('ui.guest_name') }}</th>
                <th>{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.total_price') }}</th>
                <th>{{ __('ui.platform_commission') }}</th>
                <th>{{ __('ui.net_revenue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td>
                    <td>{{ $booking->hotel_name }} / {{ $booking->room_name }}</td>
                    <td>{{ $booking->guest_name }} ({{ $booking->guests }})</td>
                    <td>{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                    <td>{{ __('ui.booking_status_' . $booking->status) }}</td>
                    <td>{{ format_price($booking->total_price, 'IDR') }}</td>
                    <td>{{ format_price($booking->platform_fee, 'IDR') }}</td>
                    <td>{{ format_price($booking->vendor_revenue, 'IDR') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">{{ __('ui.no_bookings') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection