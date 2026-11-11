@extends('layouts.app')

@section('content')
    @php
        $defaultParams = [
            'check_in'  => today()->addDay()->toDateString(),
            'check_out' => today()->addDays(2)->toDateString(),
            'guests'    => 2,
        ];
        $chip = 'px-2.5 py-1 text-[11px] font-semibold rounded-full bg-sky-50 text-sky-700 border border-sky-100';

        // Foto cadangan: dipakai kalau hotel belum punya foto sampul, atau file fotonya gagal dimuat.
        $unsplash  = fn (string $id, int $w = 700) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
        $fallbacks = [
            'photo-1540555700478-4be289fbecef',
            'photo-1582967788606-a171c1080cb0',
            'photo-1518509562904-e7ef99cdcc86',
            'photo-1590523741831-ab7e8b8f9c7f',
            'photo-1516690561799-46d8f74f9abf',
            'photo-1506929562872-bb421503ef21',
        ];
    @endphp

    {{-- HERO: foto latar penuh + form pencarian di dalamnya (sama seperti beranda) --}}
    <section class="relative bg-slate-900 text-white min-h-[520px] pt-24 pb-14 px-4 sm:px-6 lg:px-8 flex flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 z-0">
        <img src="{{ \App\Models\SiteBanner::urlFor('hotels') }}"
            onerror="this.onerror=null;this.src='{{ \App\Models\SiteBanner::defaultUrl('hotels') }}'"
            alt="" class="w-full h-full object-cover object-center scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-slate-950/75"></div>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 text-center pt-4 mb-8">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl mx-auto text-white drop-shadow-md">
                {{ __('ui.hotel_page_title') }}
            </h1>
        </div>

        <div class="max-w-6xl mx-auto w-full relative z-20">
            <div class="bg-white rounded-3xl p-5 sm:p-7 text-slate-800 shadow-2xl border border-slate-100/90 ring-1 ring-slate-900/5">
                @include('partials.search-form')
            </div>
        </div>
    </section>

    @isset($search)
        {{-- HASIL PENCARIAN (tetap di halaman Hotel) --}}
        <section id="results" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-12 scroll-mt-24">
            @include('partials.hotel-results', ['search' => $search])
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
                        <a href="{{ route('hotels.search', ['city' => $cityName] + $defaultParams) }}#results"
                           class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold hover:bg-sky-500 hover:border-sky-500 hover:text-white transition">
                            {{ $cityName }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- HOTEL UNGGULAN --}}
            <div>
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900">{{ __('ui.featured_hotels') }}</h2>
                </div>

                @if ($hotels->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                        <i class="fa-solid fa-hotel text-3xl mb-2 block"></i>
                        {{ __('ui.no_hotels_yet') }}
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($hotels as $hotel)
                            @php
                                $fallback = $unsplash($fallbacks[$loop->index % count($fallbacks)]);
                                $cover    = $hotel->coverUrl() ?: $fallback;
                            @endphp

                            <a href="{{ route('hotels.show', $hotel) . '?' . http_build_query($defaultParams) }}"
                               class="group bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col">

                                <div class="relative h-60 overflow-hidden bg-slate-200">
                                    <img src="{{ $cover }}" alt="{{ $hotel->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ $fallback }}'"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">

                                    @if ($hotel->star_rating)
                                        <div class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow">
                                            <i class="fa-solid fa-star text-amber-400"></i> {{ $hotel->star_rating }}
                                        </div>
                                    @endif
                                </div>

                                <div class="p-6 flex-1 flex flex-col gap-3">
                                    <div>
                                        <span class="text-[11px] font-black text-sky-600 uppercase tracking-wider">
                                            <i class="fa-solid fa-location-dot"></i> {{ collect([$hotel->area, $hotel->city])->filter()->join(', ') }}
                                        </span>
                                        <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 group-hover:text-sky-600 transition">{{ $hotel->name }}</h3>
                                    </div>

                                    @if ($hotel->facilities->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($hotel->facilities->take(3) as $facility)
                                                <span class="{{ $chip }}">{{ $facility->label }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">{{ __('ui.from_price') }}</span>
                                            <span class="text-lg font-black text-slate-900">{{ format_price($hotel->min_price) }}</span>
                                            <span class="text-xs text-slate-500">{{ __('ui.per_night') }}</span>
                                        </div>
                                        <span class="w-9 h-9 rounded-xl border border-slate-300 text-slate-600 flex items-center justify-center group-hover:bg-sky-500 group-hover:border-sky-500 group-hover:text-white transition">
                                            <i class="fa-solid fa-chevron-right text-[11px]"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $hotels->links() }}</div>
                @endif
            </div>
        </section>
    @endisset
@endsection