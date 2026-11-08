@extends('layouts.app')

@section('content')
    @php
        $defaultParams = [
            'pickup_date' => today()->addDay()->toDateString(),
            'return_date' => today()->addDays(3)->toDateString(),
        ];
        $chip = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold';
    @endphp

    <section class="hero-bg px-4 sm:px-6 lg:px-8 pt-10 pb-20">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white text-center mb-8">{{ __('ui.car_hero_title') }}</h1>

            <div class="glass-card rounded-3xl p-4 sm:p-6 shadow-2xl border border-white/60">
                @include('partials.car-search-form')
            </div>
        </div>
    </section>

    @isset($search)
        {{-- HASIL PENCARIAN (tetap di halaman Rental Mobil) --}}
        <section id="results" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-12 scroll-mt-24">
            @include('partials.car-results', ['search' => $search, 'resultsRoute' => 'cars.home', 'extra' => []])
        </section>
    @else
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-12 space-y-8">
            @if ($cities->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-4 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.popular_cities') }}</span>
                    @foreach ($cities as $cityName)
                        <a href="{{ route('cars.home', ['city' => $cityName] + $defaultParams) }}"
                           class="px-3 py-1.5 rounded-xl bg-sky-100 text-sagara-blue text-xs font-bold hover:bg-sky-200 transition">{{ $cityName }}</a>
                    @endforeach
                </div>
            @endif

            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-4">{{ __('ui.featured_cars') }}</h2>

                @if ($vehicles->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                        <i class="fa-solid fa-car-side text-3xl mb-2 block"></i>
                        {{ __('ui.no_cars_yet') }}
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($vehicles as $vehicle)
                            <a href="{{ route('cars.show', $vehicle) }}"
                               class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition duration-300 flex flex-col">
                                <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                                    @if ($vehicle->coverUrl())
                                        <img src="{{ $vehicle->coverUrl() }}" alt="{{ $vehicle->name }}" loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-sagara-navy to-sagara-teal flex items-center justify-center text-white/70 text-5xl">
                                            <i class="fa-solid fa-car-side"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-5 flex-1 flex flex-col gap-3">
                                    <div>
                                        <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">
                                            <i class="fa-solid fa-location-dot"></i> {{ collect([$vehicle->area, $vehicle->city])->filter()->join(', ') }}
                                        </span>
                                        <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-sagara-blue transition">{{ $vehicle->name }}</h3>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="{{ $chip }}"><i class="fa-solid fa-car-side"></i> {{ __('ui.vehicle_type_' . $vehicle->type) }}</span>
                                        <span class="{{ $chip }}"><i class="fa-solid fa-chair"></i> {{ $vehicle->seats }}</span>
                                        <span class="{{ $chip }}"><i class="fa-solid fa-gears"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                                    </div>

                                    <div class="text-[11px] font-bold text-emerald-600">
                                        <i class="fa-solid fa-id-card-clip"></i> {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}
                                    </div>

                                    <div class="mt-auto pt-3 border-t border-slate-100">
                                        <span class="text-[11px] text-slate-400 block">{{ __('ui.from_price') }}</span>
                                        <span class="text-xl font-black text-slate-900">{{ format_price($vehicle->price_per_day) }}</span>
                                        <span class="text-xs text-slate-500">{{ __('ui.per_day') }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">{{ $vehicles->links() }}</div>
                @endif
            </div>
        </section>
    @endisset
@endsection