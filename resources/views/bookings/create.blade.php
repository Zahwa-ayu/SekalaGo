@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.confirm_booking') }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>{{ $roomType->hotel->name }}</h2>
    <p>{{ $roomType->name }} ({{ __('ui.capacity') }}: {{ $roomType->capacity }})</p>
    <p>
        {{ __('ui.stay_summary', [
            'in' => $checkIn->format('d/m/Y'),
            'out' => $checkOut->format('d/m/Y'),
            'count' => $nights,
            'guests' => $guests,
        ]) }}
    </p>

    <table border="1" cellpadding="6">
        <tr>
            <td>{{ __('ui.price_per_night') }}</td>
            <td>{{ format_price($roomType->base_price) }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.nights') }}</td>
            <td>{{ $nights }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('ui.total_price') }}</strong></td>
            <td><strong>{{ format_price($roomType->base_price * $nights) }}</strong></td>
        </tr>
    </table>
    <p><small>{{ __('ui.charged_in_idr', ['amount' => format_price($roomType->base_price * $nights, 'IDR')]) }}</small></p>

    @if ($isOpen)
        <form method="POST" action="{{ route('bookings.store') }}">
            @csrf
            <input type="hidden" name="room_type" value="{{ $roomType->id }}">
            <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
            <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
            <input type="hidden" name="guests" value="{{ $guests }}">

            <p>
                <label>{{ __('ui.guest_name') }}<br>
                    <input name="guest_name"
                           value="{{ old('guest_name', auth()->user()->customer?->full_name) }}" required>
                </label>
            </p>

            <button>{{ __('ui.continue_to_payment') }}</button>
        </form>
    @else
        <p style="color:red">{{ __('ui.room_unavailable') }}</p>
    @endif
@endsection