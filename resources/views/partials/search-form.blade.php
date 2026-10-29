@php
    $params = $params ?? [];
    $cities = $cities ?? collect();
    $labelClass = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $boxClass = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition text-left';
    $inputClass = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
@endphp

@if ($errors->any())
    <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="GET" action="{{ route('hotels.search') }}">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.city') }}</label>
            <input name="city" list="city-list" class="{{ $inputClass }}"
                   value="{{ old('city', $params['city'] ?? '') }}"
                   placeholder="{{ __('ui.city') }}" required>
            <datalist id="city-list">
                @foreach ($cities as $cityName)
                    <option value="{{ $cityName }}">
                @endforeach
            </datalist>
        </div>

        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-days text-sagara-blue"></i> {{ __('ui.check_in') }}</label>
            <input type="date" name="check_in" class="{{ $inputClass }}" min="{{ today()->toDateString() }}"
                   value="{{ old('check_in', $params['check_in'] ?? today()->addDay()->toDateString()) }}" required>
        </div>

        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-check text-sagara-coral"></i> {{ __('ui.check_out') }}</label>
            <input type="date" name="check_out" class="{{ $inputClass }}" min="{{ today()->addDay()->toDateString() }}"
                   value="{{ old('check_out', $params['check_out'] ?? today()->addDays(2)->toDateString()) }}" required>
        </div>

        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-users text-sagara-blue"></i> {{ __('ui.guests') }}</label>
            <input type="number" name="guests" class="{{ $inputClass }}" min="1" max="20"
                   value="{{ old('guests', $params['guests'] ?? 2) }}" required>
        </div>
    </div>

    <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
        <button class="w-full md:w-auto px-8 py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
            <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.search') }}
        </button>
    </div>
</form>