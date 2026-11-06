@php
    $onCars = request()->routeIs('cars.*');
    $tab    = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition';
@endphp

<div class="flex gap-2 mb-4">
    <a href="{{ route('home') }}"
       class="{{ $tab }} {{ ! $onCars ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
        <i class="fa-solid fa-hotel"></i> {{ __('ui.tab_hotels') }}
    </a>
    <a href="{{ route('cars.home') }}"
       class="{{ $tab }} {{ $onCars ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
        <i class="fa-solid fa-car"></i> {{ __('ui.tab_cars') }}
    </a>
</div>