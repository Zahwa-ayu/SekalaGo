@extends('layouts.app')

@section('content')
    @php
        $defaultParams = [
            'check_in'  => today()->addDay()->toDateString(),
            'check_out' => today()->addDays(2)->toDateString(),
            'guests'    => 2,
        ];
        $chip = 'px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-sky-100 text-sagara-blue';
    @endphp

    <section class="hero-bg px-4 sm:px-6 lg:px-8 pt-10 pb-20">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white text-center mb-8">{{ __('ui.hotel_page_title') }}</h1>

            <div class="glass-card rounded-3xl p-4 sm:p-6 shadow-2xl border border-white/60">
                @include('partials.search-form')
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-12 space-y-8">
        @if ($cities->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-4 flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.popular_cities') }}</span>
                @foreach ($cities as $cityName)
                    <a href="{{ route('hotels.search', ['city' => $cityName] + $defaultParams) }}"
                       class="px-3 py-1.5 rounded-xl bg-sky-100 text-sagara-blue text-xs font-bold hover:bg-sky-200 transition">{{ $cityName }}</a>
                @endforeach
            </div>
        @endif

        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-4">{{ __('ui.featured_hotels') }}</h2>

            @if ($hotels->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                    <i class="fa-solid fa-hotel text-3xl mb-2 block"></i>
                    {{ __('ui.no_hotels_yet') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($hotels as $hotel)
                        <a href="{{ route('hotels.show', $hotel) }}"
                           class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition duration-300 flex flex-col">
                            <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                                @if ($hotel->coverUrl())
                                    <img src="{{ $hotel->coverUrl() }}" alt="{{ $hotel->name }}" loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-sagara-navy to-sagara-teal flex items-center justify-center text-white/70 text-5xl">
                                        <i class="fa-solid fa-hotel"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5 flex-1 flex flex-col gap-3">
                                <div>
                                    <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">
                                        <i class="fa-solid fa-location-dot"></i> {{ collect([$hotel->area, $hotel->city])->filter()->join(', ') }}
                                    </span>
                                    <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-sagara-blue transition">{{ $hotel->name }}</h3>
                                    @if ($hotel->star_rating)
                                        <span class="text-sm text-sagara-gold">
                                            @for ($i = 0; $i < $hotel->star_rating; $i++)<i class="fa-solid fa-star"></i>@endfor
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($hotel->facilities->take(3) as $facility)
                                        <span class="{{ $chip }}">{{ $facility->label }}</span>
                                    @endforeach
                                </div>

                                <div class="mt-auto pt-3 border-t border-slate-100">
                                    <span class="text-[11px] text-slate-400 block">{{ __('ui.from_price') }}</span>
                                    <span class="text-xl font-black text-slate-900">{{ format_price($hotel->min_price) }}</span>
                                    <span class="text-xs text-slate-500">{{ __('ui.per_night') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">{{ $hotels->links() }}</div>
            @endif
        </div>
    </section>
@endsection