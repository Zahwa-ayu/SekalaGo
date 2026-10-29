@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.payment') }}</h1>

    <p>
        <strong>{{ $booking->booking_code }}</strong><br>
        {{ $booking->hotel_name }} / {{ $booking->room_name }}<br>
        {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
        ({{ __('ui.for_nights', ['count' => $booking->nights]) }})
    </p>

    <p>
        {{ __('ui.total_price') }}: <strong>{{ format_price($booking->total_price, 'IDR') }}</strong><br>
        <small>{{ __('ui.price_estimate', ['amount' => $booking->displayTotal()]) }}</small>
    </p>

    <p>{{ __('ui.pay_before', ['time' => $booking->expires_at->format('H:i')]) }}</p>

    <p><small>{{ __('ui.sandbox_notice') }}</small></p>

    <form method="POST" action="{{ route('bookings.pay.process', $booking) }}" style="display:inline">
        @csrf
        <input type="hidden" name="outcome" value="success">
        <button>{{ __('ui.pay_success_btn') }}</button>
    </form>

    <form method="POST" action="{{ route('bookings.pay.process', $booking) }}" style="display:inline">
        @csrf
        <input type="hidden" name="outcome" value="fail">
        <button>{{ __('ui.pay_fail_btn') }}</button>
    </form>
@endsection