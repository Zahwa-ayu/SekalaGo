@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.my_bookings') }}</h1>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.booking_code') }}</th>
                <th>{{ __('ui.hotel') }}</th>
                <th>{{ __('ui.check_in') }}</th>
                <th>{{ __('ui.check_out') }}</th>
                <th>{{ __('ui.total_price') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td>
                    <td>{{ $booking->hotel_name }}</td>
                    <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                    <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                    <td>{{ $booking->displayTotal() }}</td>
                    <td>{{ __('ui.booking_status_' . $booking->status) }}</td>
                    <td>
                        <a href="{{ route('bookings.show', $booking) }}">{{ __('ui.view') }}</a>
                        @if ($booking->status === 'pending_payment')
                            | <a href="{{ route('bookings.pay', $booking) }}">{{ __('ui.pay_now') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">{{ __('ui.no_bookings') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection