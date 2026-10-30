@extends('layouts.app')

@section('content')
    <p><a href="{{ route('bookings.index') }}">&larr; {{ __('ui.my_bookings') }}</a></p>

    <h1>{{ $booking->booking_code }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

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

    @if ($booking->status === 'cancelled')
        <p>
            <strong>{{ $booking->cancelled_by === 'admin' ? __('ui.cancelled_by_admin') : __('ui.cancelled_by_customer') }}</strong>
            ({{ $booking->cancelled_at->format('d/m/Y H:i') }})<br>
            @if ($booking->cancellation_reason)
                {{ __('ui.cancellation_reason_label') }}: {{ $booking->cancellation_reason }}<br>
            @endif
            @if ($booking->refund_amount > 0)
                {{ __('ui.refund_amount') }}: <strong>{{ format_price($booking->refund_amount, 'IDR') }}</strong>
                ({{ $booking->refund_reference }})
            @endif
        </p>
    @endif

    @if ($booking->status === 'pending_payment')
        <p><a href="{{ route('bookings.pay', $booking) }}">{{ __('ui.pay_now') }}</a></p>

        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
              onsubmit="return confirm(@js(__('ui.cancel_confirm')))">
            @csrf
            <button>{{ __('ui.cancel_booking') }}</button>
        </form>
    @elseif ($booking->status === 'confirmed')
        <p><a href="{{ route('bookings.ticket', $booking) }}">{{ __('ui.download_ticket') }}</a></p>

        <h2>{{ __('ui.cancel_booking') }}</h2>

        @if ($booking->canBeCancelledByCustomer())
            <p>
                {{ __('ui.free_cancel_until', ['date' => $booking->freeCancelDeadline()->format('d/m/Y')]) }}<br>
                {{ __('ui.refund_full_notice', ['amount' => format_price($booking->total_price, 'IDR')]) }}
            </p>

            <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                  onsubmit="return confirm(@js(__('ui.cancel_confirm')))">
                @csrf
                <p>
                    <textarea name="cancellation_reason" rows="2" maxlength="500"
                              placeholder="{{ __('ui.cancel_reason') }}"></textarea>
                </p>
                <button>{{ __('ui.cancel_booking') }}</button>
            </form>
        @else
            <p>
                {{ __('ui.free_cancel_until', ['date' => $booking->freeCancelDeadline()->format('d/m/Y')]) }}<br>
                <span style="color:red">{{ __('ui.cancel_not_allowed') }}</span>
            </p>
        @endif
    @endif
@endsection