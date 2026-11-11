@extends('layouts.app')

@section('content')
    @php
        $defaultParams = [
            'pickup_date' => today()->addDay()->toDateString(),
            'return_date' => today()->addDays(3)->toDateString(),
        ];
        $chip = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 border border-slate-200 text-[11px] font-semibold';

        // Foto cadangan: dipakai kalau kendaraan belum punya foto sampul, atau file fotonya gagal dimuat.
        $unsplash  = fn (string $id, int $w = 700) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
        $fallbacks = [
            'photo-1544551763-46a013bb70d5',
            'photo-1494976388531-d1058494cdd8',
            'photo-1533473359331-0135ef1b58bf',
            'photo-1549317661-bd32c8ce0db2',
            'photo-1503376780353-7e6692767b70',
            'photo-1552519507-da3b142c6e3d',
        ];
    @endphp

    {{-- HERO: foto latar penuh + form pencarian di dalamnya (sama seperti beranda) --}}
    <section class="relative bg-slate-900 text-white min-h-[520px] pt-24 pb-14 px-4 sm:px-6 lg:px-8 flex flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 z-0">
        <img src="{{ \App\Models\SiteBanner::urlFor('cars') }}"
            onerror="this.onerror=null;this.src='{{ \App\Models\SiteBanner::defaultUrl('cars') }}'"
            alt="" class="w-full h-full object-cover object-center scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-slate-950/75"></div>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 text-center pt-4 mb-8">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl mx-auto text-white drop-shadow-md">
                {{ __('ui.car_hero_title') }}
            </h1>
        </div>

        <div class="max-w-6xl mx-auto w-full relative z-20">
            <div class="bg-white rounded-3xl p-5 sm:p-7 text-slate-800 shadow-2xl border border-slate-100/90 ring-1 ring-slate-900/5">
                @include('partials.car-search-form')
            </div>
        </div>
    </section>

    @isset($search)
        {{-- HASIL PENCARIAN (tetap di halaman Rental Mobil) --}}
        <section id="results" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-12 scroll-mt-24">
            @include('partials.car-results', ['search' => $search, 'resultsRoute' => 'cars.home', 'extra' => []])
        </section>
    @else
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-12 space-y-14">

            {{-- KOTA POPULER --}}
            @if ($cities->isNotEmpty())
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 px-6 py-5 flex flex-wrap items-center gap-2.5">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-sky-600 mr-1">
                        <i class="fa-solid fa-location-dot"></i> {{ __('ui.popular_cities') }}
                    </span>
                    @foreach ($cities as $cityName)
                        <a href="{{ route('cars.home', ['city' => $cityName] + $defaultParams) }}#results"
                           class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold hover:bg-sky-500 hover:border-sky-500 hover:text-white transition">
                            {{ $cityName }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- ARMADA UNGGULAN --}}
            <div>
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900">{{ __('ui.featured_cars') }}</h2>
                </div>

                @if ($vehicles->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                        <i class="fa-solid fa-car-side text-3xl mb-2 block"></i>
                        {{ __('ui.no_cars_yet') }}
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($vehicles as $vehicle)
                            @php
                                $fallback = $unsplash($fallbacks[$loop->index % count($fallbacks)]);
                                $cover    = $vehicle->coverUrl() ?: $fallback;
                            @endphp

                            <a href="{{ route('cars.show', $vehicle) . '?' . http_build_query($defaultParams) }}"
                               class="group bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col">

                                <div class="relative h-72 overflow-hidden bg-slate-200">
                                    <img src="{{ $cover }}" alt="{{ $vehicle->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ $fallback }}'"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">

                                    <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full shadow">
                                        {{ __('ui.vehicle_type_' . $vehicle->type) }}
                                    </span>
                                </div>

                                <div class="p-6 flex-1 flex flex-col gap-3">
                                    <div>
                                        <span class="text-[11px] font-black text-sky-600 uppercase tracking-wider">
                                            <i class="fa-solid fa-location-dot"></i> {{ collect([$vehicle->area, $vehicle->city])->filter()->join(', ') }}
                                        </span>
                                        <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 group-hover:text-sky-600 transition">{{ $vehicle->name }}</h3>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="{{ $chip }}"><i class="fa-solid fa-chair"></i> {{ $vehicle->seats }}</span>
                                        <span class="{{ $chip }}"><i class="fa-solid fa-gears"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                                    </div>

                                    <div class="text-[11px] font-bold text-emerald-600">
                                        <i class="fa-solid fa-id-card-clip"></i> {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}
                                    </div>

                                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">{{ __('ui.from_price') }}</span>
                                            <span class="text-lg font-black text-slate-900">{{ format_price($vehicle->price_per_day) }}</span>
                                            <span class="text-xs text-slate-500">{{ __('ui.per_day') }}</span>
                                        </div>
                                        <span class="w-9 h-9 rounded-xl border border-slate-300 text-slate-600 flex items-center justify-center group-hover:bg-sky-500 group-hover:border-sky-500 group-hover:text-white transition">
                                            <i class="fa-solid fa-chevron-right text-[11px]"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $vehicles->links() }}</div>
                @endif
            </div>
        </section>
    @endisset
@endsection