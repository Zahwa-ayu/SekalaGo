@php
    $params = $params ?? [];
    $cities = $cities ?? collect();
@endphp

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="GET" action="{{ route('hotels.search') }}">
    <input name="city" list="city-list"
           value="{{ old('city', $params['city'] ?? '') }}"
           placeholder="{{ __('ui.city') }}" required>
    <datalist id="city-list">
        @foreach ($cities as $cityName)
            <option value="{{ $cityName }}">
        @endforeach
    </datalist>

    <label>{{ __('ui.check_in') }}
        <input type="date" name="check_in" min="{{ today()->toDateString() }}"
               value="{{ old('check_in', $params['check_in'] ?? today()->addDay()->toDateString()) }}" required>
    </label>

    <label>{{ __('ui.check_out') }}
        <input type="date" name="check_out" min="{{ today()->addDay()->toDateString() }}"
               value="{{ old('check_out', $params['check_out'] ?? today()->addDays(2)->toDateString()) }}" required>
    </label>

    <label>{{ __('ui.guests') }}
        <input type="number" name="guests" min="1" max="20"
               value="{{ old('guests', $params['guests'] ?? 2) }}" required>
    </label>

    <button>{{ __('ui.search') }}</button>
</form>