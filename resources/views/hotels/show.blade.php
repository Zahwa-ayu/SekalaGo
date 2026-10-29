@extends('layouts.app')

@section('content')
    <p>
        <a href="{{ $params ? route('hotels.search', $params + ['city' => $params['city'] ?? $hotel->city]) : route('home') }}">
            &larr; {{ __('ui.search_results') }}
        </a>
    </p>

    <h1>
        {{ $hotel->name }}
        @if ($hotel->star_rating) ({{ $hotel->star_rating }}★) @endif
    </h1>

    <p>{{ $hotel->city }}@if ($hotel->address), {{ $hotel->address }}@endif</p>
    <p>{{ $hotel->description }}</p>
    <p><strong>{{ __('ui.facilities') }}:</strong>
        {{ $hotel->facilities->map(fn ($f) => $f->label)->join(', ') ?: '-' }}
    </p>

    @if ($params)
        <p>
            {{ __('ui.stay_summary', [
                'in' => \Illuminate\Support\Carbon::parse($params['check_in'])->format('d/m/Y'),
                'out' => \Illuminate\Support\Carbon::parse($params['check_out'])->format('d/m/Y'),
                'count' => $nights,
                'guests' => $params['guests'],
            ]) }}
        </p>
    @else
        <p><small>{{ __('ui.pick_dates_hint') }}</small></p>
    @endif

    <h2>{{ __('ui.room_types') }}</h2>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.room_name') }}</th>
                <th>{{ __('ui.capacity') }}</th>
                <th>{{ __('ui.price_per_night') }}</th>
                @if ($params)
                    <th>{{ __('ui.total_price') }}</th>
                    <th>{{ __('ui.status') }}</th>
                @endif
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roomTypes as $roomType)
                <tr>
                    <td>
                        {{ $roomType->name }}
                        @if ($roomType->description)
                            <br><small>{{ $roomType->description }}</small>
                        @endif
                    </td>
                    <td>{{ $roomType->capacity }}</td>
                    <td>{{ format_price($roomType->base_price) }}</td>
                    @if ($params)
                        <td>{{ format_price($roomType->base_price * $nights) }}</td>
                        <td>{{ $roomType->is_open ? __('ui.available') : __('ui.not_available') }}</td>
                    @endif
                    <td>
                        @if ($params && $roomType->is_open)
                            <a href="{{ route('bookings.create', [
                                'room_type' => $roomType->id,
                                'check_in' => $params['check_in'],
                                'check_out' => $params['check_out'],
                                'guests' => $params['guests'],
                            ]) }}">{{ __('ui.book') }}</a>
                        @else
                            <button disabled>{{ __('ui.book') }}</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('ui.no_room_types') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection