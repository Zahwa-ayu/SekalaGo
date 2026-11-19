@php
    $isCars   = $kind === 'cars';
    $from     = \Illuminate\Support\Carbon::parse($params[$isCars ? 'pickup_date' : 'check_in']);
    $to       = \Illuminate\Support\Carbon::parse($params[$isCars ? 'return_date' : 'check_out']);
    $driver   = $params['driver'] ?? null;

    // Kembali = hapus pencarian dan buka tampilan awal halaman ini (di Beranda tab mobil memakai #cars)
    $backUrl  = route(\Illuminate\Support\Facades\Route::currentRouteName())
              . ($isCars && request()->routeIs('home') ? '#cars' : '#booking');

    $chip = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100 text-[11px] font-semibold';
    $btn  = 'inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold shadow-sm transition';
@endphp

<div class="flex flex-wrap items-center gap-2">
    <a href="{{ $backUrl }}" class="{{ $btn }}"><i class="fa-solid fa-arrow-left"></i> {{ __('ui.search_back') }}</a>
    <a href="#booking" class="{{ $btn }}"><i class="fa-solid fa-pen"></i> {{ __('ui.search_change') }}</a>

    <div class="flex flex-wrap gap-1.5 sm:ml-2">
        <span class="{{ $chip }}"><i class="fa-solid fa-location-dot"></i> {{ $params['city'] }}</span>
        <span class="{{ $chip }}"><i class="fa-solid fa-calendar-days"></i> {{ $from->format('d/m') }} - {{ $to->format('d/m/Y') }}</span>
        <span class="{{ $chip }}">{{ $count }} {{ __($isCars ? 'ui.calendar_unit_cars' : 'ui.calendar_unit_hotels') }}</span>

        @if (! $isCars && ! empty($params['guests']))
            <span class="{{ $chip }}"><i class="fa-solid fa-user-group"></i> {{ $params['guests'] }} {{ __('ui.guests') }}</span>
        @endif

        @if ($isCars && $driver)
            <span class="{{ $chip }}">{{ __($driver === 'with_driver' ? 'ui.driver_option_with' : 'ui.driver_option_self') }}</span>
        @endif
    </div>
</div>