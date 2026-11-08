@php
    $params = $params ?? [];
    $cities = $cities ?? collect();

    $box   = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
@endphp

@if ($errors->any())
    <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="GET" action="{{ $action ?? route('cars.home') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
    @if (! empty($tab))
        <input type="hidden" name="tab" value="{{ $tab }}">
    @endif

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.city') }}</label>
        <input name="city" list="car-city-list" required class="{{ $input }}"
               value="{{ old('city', $params['city'] ?? '') }}" placeholder="{{ __('ui.city') }}">
        <datalist id="car-city-list">
            @foreach ($cities as $cityName)
                <option value="{{ $cityName }}">
            @endforeach
        </datalist>
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-calendar-day text-sagara-blue"></i> {{ __('ui.pickup_date') }}</label>
        <input type="date" name="pickup_date" required class="{{ $input }}" min="{{ today()->toDateString() }}"
               value="{{ old('pickup_date', $params['pickup_date'] ?? today()->addDay()->toDateString()) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-calendar-check text-sagara-blue"></i> {{ __('ui.return_date') }}</label>
        <input type="date" name="return_date" required class="{{ $input }}" min="{{ today()->addDay()->toDateString() }}"
               value="{{ old('return_date', $params['return_date'] ?? today()->addDays(3)->toDateString()) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-id-card-clip text-sagara-blue"></i> {{ __('ui.driver_mode') }}</label>
        <select name="driver" class="{{ $input }}">
            <option value="">{{ __('ui.driver_any') }}</option>
            <option value="with_driver" @selected(old('driver', $params['driver'] ?? '') === 'with_driver')>{{ __('ui.driver_option_with') }}</option>
            <option value="self_drive" @selected(old('driver', $params['driver'] ?? '') === 'self_drive')>{{ __('ui.driver_option_self') }}</option>
        </select>
    </div>

    <button class="px-6 py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.search') }}
    </button>
</form>