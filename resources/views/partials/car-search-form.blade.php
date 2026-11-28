@php
    $params = $params ?? [];
    $cities = $cities ?? collect();

    $box   = 'bg-slate-50 hover:bg-sky-50/40 p-3.5 rounded-2xl border border-slate-200/80 focus-within:border-sky-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-sky-100 transition text-left';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-bold text-slate-800 text-sm focus:outline-none cursor-pointer';
@endphp

@if ($errors->any())
    <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="GET" action="{{ $action ?? route('cars.home') }}" class="w-full">
    @if (! empty($tab))
        <input type="hidden" name="tab" value="{{ $tab }}">
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
        <div class="{{ $box }}">
            <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sky-500"></i> {{ __('ui.city') }}</label>
            <input name="city" list="car-city-list" required class="{{ $input }}"
                   value="{{ old('city', $params['city'] ?? '') }}" placeholder="{{ __('ui.city') }}">
            @include('partials.city-datalist', ['id' => 'car-city-list', 'for' => 'cars'])
        </div>

        {{-- Satu kolom tanggal: kalender rentang dengan harga per hari --}}
        @include('partials.date-range-picker', [
            'type'      => 'cars',
            'class'     => 'md:col-span-2',
            'label'     => __('ui.pickup_date') . ' - ' . __('ui.return_date'),
            'startName' => 'pickup_date',
            'endName'   => 'return_date',
            'start'     => old('pickup_date', $params['pickup_date'] ?? today()->addDay()->toDateString()),
            'end'       => old('return_date', $params['return_date'] ?? today()->addDays(3)->toDateString()),
        ])

        <div class="{{ $box }}">
            <label class="{{ $label }}"><i class="fa-solid fa-id-card-clip text-sky-500"></i> {{ __('ui.driver_mode') }}</label>
            <select name="driver" class="{{ $input }}">
                <option value="">{{ __('ui.driver_any') }}</option>
                <option value="with_driver" @selected(old('driver', $params['driver'] ?? '') === 'with_driver')>{{ __('ui.driver_option_with') }}</option>
                <option value="self_drive" @selected(old('driver', $params['driver'] ?? '') === 'self_drive')>{{ __('ui.driver_option_self') }}</option>
            </select>
        </div>
    </div>

    {{-- Tombol cari di bawah, solid biru (sama seperti form hotel) --}}
    <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
        <button class="w-full md:w-auto px-8 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow-md shadow-sky-500/20 transition duration-300 flex items-center justify-center gap-2 text-sm">
            <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.search') }} Mobil
        </button>
    </div>
</form>