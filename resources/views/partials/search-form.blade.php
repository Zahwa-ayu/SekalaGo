@php
    $params = $params ?? [];
    $labelClass = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $boxClass = 'bg-slate-50 hover:bg-sky-50/40 p-3.5 rounded-2xl border border-slate-200/80 focus-within:border-sky-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-sky-100 transition text-left';
    $inputClass = 'w-full bg-transparent font-bold text-slate-800 text-sm focus:outline-none cursor-pointer';
@endphp

@if ($errors->any())
    <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

{{-- FORM SEARCH HOTEL (tab Hotel / Mobil diatur oleh partials/search-widget) --}}
<form method="GET" action="{{ $action ?? route('hotels.search') }}" class="w-full">
    @if (! empty($tab))
        <input type="hidden" name="tab" value="{{ $tab }}">
    @endif
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-location-dot text-sky-500"></i> {{ __('ui.city') }}</label>
            <input name="city" list="city-list" class="{{ $inputClass }}"
                   value="{{ old('city', $params['city'] ?? '') }}"
                   placeholder="Mau menginap di mana?" required>
            @include('partials.city-datalist', ['id' => 'city-list', 'for' => 'hotels'])
        </div>

        @include('partials.date-range-picker', [
            'type'      => 'hotels',
            'class'     => 'md:col-span-2',
            'label'     => __('ui.check_in') . ' - ' . __('ui.check_out'),
            'startName' => 'check_in',
            'endName'   => 'check_out',
            'start'     => old('check_in', $params['check_in'] ?? today()->addDay()->toDateString()),
            'end'       => old('check_out', $params['check_out'] ?? today()->addDays(2)->toDateString()),
        ])

        <div class="{{ $boxClass }}">
            <label class="{{ $labelClass }}"><i class="fa-solid fa-users text-sky-500"></i> {{ __('ui.guests') }}</label>
            <input type="number" name="guests" class="{{ $inputClass }}" min="1" max="20"
                   value="{{ old('guests', $params['guests'] ?? 2) }}" required>
        </div>
    </div>

    <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
        <button class="w-full md:w-auto px-8 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow-md shadow-sky-500/20 transition duration-300 flex items-center justify-center gap-2 text-sm">
            <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.search') }} Hotel
        </button>
    </div>
</form>