@extends('layouts.app')

@section('content')
    <p><a href="{{ route('bookings.index') }}">&larr; {{ __('ui.my_bookings') }}</a></p>

    <h1>{{ $booking->booking_code }}</h1>

    <p>
        <strong>{{ __('ui.status') }}:</strong> {{ __('ui.booking_status_' . $booking->status) }}<br>
        <strong>{{ __('ui.payment') }}:</strong> {{ __('ui.payment_status_' . $booking->payment_status) }}
        @if ($booking->payment_reference) ({{ $booking->payment_reference }}) @endif
    </p>

    <p>
        <strong>{{ $booking->hotel_name }}</strong> / {{ $booking->room_name }}<br>
        {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
        ({{ __('ui.for_nights', ['count' => $booking->nights]) }}), {{ $booking->guests }} {{ __('ui.guests') }}<br>
        {{ __('ui.guest_name') }}: {{ $booking->guest_name }}
    </p>

    <p>
        {{ __('ui.total_price') }}: <strong>{{ $booking->displayTotal() }}</strong><br>
        <small>{{ __('ui.charged_in_idr', ['amount' => format_price($booking->total_price, 'IDR')]) }}</small>
    </p>

    @if ($booking->status === 'pending_payment')
        <p><a href="{{ route('bookings.pay', $booking) }}">{{ __('ui.pay_now') }}</a></p>
    @endif
@endsection