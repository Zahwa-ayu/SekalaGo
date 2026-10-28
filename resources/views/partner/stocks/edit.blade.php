@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.stock') }}: {{ $roomType->hotel->name }} / {{ $roomType->name }}</h1>

    <p>
        <a href="{{ route('partner.hotels.room-types.index', $roomType->hotel) }}">{{ __('ui.room_types') }}</a>
    </p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>{{ __('ui.bulk_stock') }}</h2>

    <form method="POST" action="{{ route('partner.room-types.stocks.update', $roomType) }}">
        @csrf
        @method('PUT')

        <p>
            <label>{{ __('ui.start_date') }}<br>
                <input type="date" name="start_date"
                       min="{{ today()->toDateString() }}"
                       value="{{ old('start_date', today()->toDateString()) }}" required>
            </label>
        </p>

        <p>
            <label>{{ __('ui.end_date') }}<br>
                <input type="date" name="end_date"
                       min="{{ today()->toDateString() }}"
                       value="{{ old('end_date', today()->addDays(29)->toDateString()) }}" required>
            </label>
        </p>

        <p>
            <label>{{ __('ui.available') }} (0 - {{ $roomType->total_rooms }})<br>
                <input type="number" name="available" min="0" max="{{ $roomType->total_rooms }}"
                       value="{{ old('available', $roomType->total_rooms) }}" required>
            </label>
            <br><small>{{ __('ui.stock_hint') }}</small>
        </p>

        <fieldset>
            <legend>{{ __('ui.apply_to_days') }}</legend>
            @foreach ([1, 2, 3, 4, 5, 6, 0] as $day)
                <label>
                    <input type="checkbox" name="days[]" value="{{ $day }}"
                           @checked(in_array($day, old('days', [0, 1, 2, 3, 4, 5, 6])))>
                    {{ __('ui.days')[$day] }}
                </label>
            @endforeach
        </fieldset>

        <p><button>{{ __('ui.save') }}</button></p>
    </form>

    <h2>{{ __('ui.current_stock') }}</h2>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.date') }}</th>
                <th>{{ __('ui.available') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ranges as $range)
                <tr>
                    <td>
                        @if ($range['from']->isSameDay($range['to']))
                            {{ $range['from']->format('d/m/Y') }}
                        @else
                            {{ $range['from']->format('d/m/Y') }} – {{ $range['to']->format('d/m/Y') }}
                        @endif
                    </td>
                    <td>{{ $range['available'] }}</td>
                </tr>
            @empty
                <tr><td colspan="2">{{ __('ui.no_stock') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection