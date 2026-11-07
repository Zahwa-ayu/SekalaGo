@php
    $authUser = auth()->user();

    $useDashboard = $authUser && (
        request()->routeIs('admin.*', 'partner.*')
        || (request()->routeIs('corporate.*') && $authUser->companyUser?->isFinance())
    );

    // Parameter bawaan supaya tautan langsung menampilkan harga & ketersediaan
    $hotelParams = [
        'check_in'  => today()->addDay()->toDateString(),
        'check_out' => today()->addDays(2)->toDateString(),
        'guests'    => 2,
    ];
    $carParams = [
        'pickup_date' => today()->addDay()->toDateString(),
        'return_date' => today()->addDays(3)->toDateString(),
    ];

    // Foto cadangan: dipakai kalau data belum punya foto, atau file fotonya gagal dimuat.
    $unsplash       = fn (string $id, int $w = 700) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    $hotelFallbacks = [
        'photo-1540555700478-4be289fbecef', 'photo-1582967788606-a171c1080cb0', 'photo-1518509562904-e7ef99cdcc86',
        'photo-1590523741831-ab7e8b8f9c7f', 'photo-1516690561799-46d8f74f9abf', 'photo-1506929562872-bb421503ef21',
    ];
    $carFallbacks = [
        'photo-1544551763-46a013bb70d5', 'photo-1494976388531-d1058494cdd8', 'photo-1533473359331-0135ef1b58bf',
    ];

    $chip = 'px-2.5 py-1 text-[11px] font-semibold rounded-full bg-sky-50 text-sky-700 border border-sky-100';
@endphp
@extends($useDashboard ? 'layouts.dashboard' : 'layouts.storefront')

@section('content')

    {{-- HERO WITH BACKGROUND GAMBAR UTUH MEMBUNGKUS SEARCH BAR --}}
    <section class="relative bg-slate-900 text-white min-h-[640px] pt-24 pb-16 px-4 sm:px-6 lg:px-8 flex flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80" 
                 alt="Background Alam Travel" 
                 class="w-full h-full object-cover object-center scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-slate-950/75"></div>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 text-center pt-6 mb-8">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl mx-auto mb-3 text-white drop-shadow-md">
                {{ __('ui.hero_title') }}
            </h1>
            <p class="text-base sm:text-lg text-slate-100 max-w-xl mx-auto font-medium drop-shadow-sm">
                Temukan hotel impian & rental mobil terbaik untuk liburan Anda.
            </p>
        </div>

        <div class="max-w-6xl mx-auto w-full relative z-20 pb-4">
            <div id="booking" class="bg-white rounded-3xl p-5 sm:p-7 text-slate-800 shadow-2xl border border-slate-100/90 ring-1 ring-slate-900/5">
                @include('partials.search-widget')
            </div>
        </div>
    </section>

    {{-- KOTA FAVORIT (data asli: jumlah hotel & armada per kota) --}}
    @if ($cityStats->isNotEmpty())
        <section id="radar" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 mb-20">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                    <div>
                        <div class="inline-flex items-center gap-2 text-sky-600 font-extrabold text-xs uppercase tracking-widest mb-1">
                            <span class="relative flex h-2.5 w-2.5">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            Status Ketersediaan Real-Time
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">Kota Favorit & Availability Hari Ini</h2>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm max-w-md">Pantau ketersediaan hotel dan unit kendaraan sebelum memesan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($cityStats as $stat)
                        @php
                            $badge = $stat['hotels'] > 0 && $stat['vehicles'] > 0
                                ? 'Hotel & Mobil'
                                : ($stat['hotels'] > 0 ? 'Kamar Tersedia' : 'Unit Tersedia');
                        @endphp
                        <div class="bg-slate-50 hover:bg-sky-50/60 p-5 rounded-2xl border border-slate-200/80 hover:border-sky-300 transition duration-300 flex flex-col justify-between group">
                            <div>
                                <div class="flex justify-between items-start mb-3 gap-2">
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-slate-900 text-sm group-hover:text-sky-600 transition truncate">{{ $stat['city'] }}</h3>
                                        <p class="text-[11px] text-slate-500 font-medium">{{ $stat['hotels'] + $stat['vehicles'] }} pilihan tersedia</p>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-extrabold rounded-full whitespace-nowrap">{{ $badge }}</span>
                                </div>
                                <div class="flex items-baseline gap-2 my-3">
                                    <span class="text-2xl font-black tracking-tight {{ $stat['hotels'] > 0 ? 'text-slate-900' : 'text-slate-300' }}">{{ $stat['hotels'] }} Hotel</span>
                                </div>
                                <p class="text-xs font-semibold flex items-center gap-1 mb-2 {{ $stat['vehicles'] > 0 ? 'text-sky-600' : 'text-slate-300' }}">
                                    <i class="fa-solid fa-car-side"></i> {{ $stat['vehicles'] }} Unit Mobil
                                </p>
                            </div>
                            <div class="flex justify-between text-[11px] font-medium text-slate-500 pt-3 border-t border-slate-200/60">
                                @if ($stat['hotels'] > 0)
                                    <a href="{{ route('hotels.search', ['city' => $stat['city']] + $hotelParams) }}" class="flex items-center gap-1.5 hover:text-sky-600 transition">
                                        <i class="fa-solid fa-hotel text-slate-400"></i> Cari Hotel
                                    </a>
                                @else
                                    <span class="flex items-center gap-1.5 text-slate-300"><i class="fa-solid fa-hotel"></i> Cari Hotel</span>
                                @endif

                                @if ($stat['vehicles'] > 0)
                                    <a href="{{ route('cars.search', ['city' => $stat['city']] + $carParams) }}" class="flex items-center gap-1.5 hover:text-sky-600 transition">
                                        <i class="fa-solid fa-car-side text-slate-400"></i> Cari Mobil
                                    </a>
                                @else
                                    <span class="flex items-center gap-1.5 text-slate-300"><i class="fa-solid fa-car-side"></i> Cari Mobil</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ARMADA PILIHAN (mobil termurah dari database) --}}
    @if ($featuredVehicles->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24 {{ $cityStats->isEmpty() ? 'pt-16' : '' }}">
            <div class="bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 relative z-10">
                    <div>
                        <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] uppercase tracking-widest font-black px-3.5 py-1.5 rounded-full inline-block mb-3">Armada Pilihan</span>
                        <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white">Rental Mobil Harga Terbaik</h2>
                        <p class="text-slate-400 text-xs sm:text-sm mt-1">Unit terverifikasi dengan harga mulai dari yang paling hemat.</p>
                    </div>
                    <a href="{{ route('cars.home') }}"
                       class="self-start sm:self-auto px-5 py-3 text-xs font-bold bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow transition inline-flex items-center gap-2">
                        Lihat Semua Mobil <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    @foreach ($featuredVehicles as $vehicle)
                        @php
                            $fallback = $unsplash($carFallbacks[$loop->index % count($carFallbacks)], 600);
                            $cover    = $vehicle->coverUrl() ?: $fallback;
                        @endphp
                        <a href="{{ route('cars.show', $vehicle) . '?' . http_build_query($carParams) }}"
                           class="bg-white text-slate-800 rounded-2xl overflow-hidden shadow-lg group hover:-translate-y-2 transition duration-300 flex flex-col">
                            <div class="relative h-48 overflow-hidden bg-slate-200">
                                <img src="{{ $cover }}" alt="{{ $vehicle->name }}" loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $fallback }}'"
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                <span class="absolute top-3 left-3 bg-amber-500 text-slate-950 text-xs font-black px-3 py-1 rounded-lg shadow-md">
                                    {{ __('ui.vehicle_type_' . $vehicle->type) }}
                                </span>
                                @if ($vehicle->city)
                                    <span class="absolute bottom-3 left-3 bg-slate-900/90 text-white font-medium text-[11px] px-2.5 py-1 rounded-md backdrop-blur-sm">
                                        <i class="fa-solid fa-location-dot"></i> {{ $vehicle->city }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-extrabold text-slate-900 text-base mb-2 leading-snug">{{ $vehicle->name }}</h3>
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    <span class="{{ $chip }}"><i class="fa-solid fa-chair"></i> {{ $vehicle->seats }}</span>
                                    <span class="{{ $chip }}"><i class="fa-solid fa-gears"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                                    <span class="{{ $chip }}"><i class="fa-solid fa-id-card-clip"></i> {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}</span>
                                </div>

                                <div class="mt-auto flex items-end justify-between pt-3 border-t border-slate-100">
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">{{ __('ui.from_price') }}</span>
                                        <div class="text-lg font-black text-slate-900">{{ format_price($vehicle->price_per_day) }} <span class="text-[11px] font-medium text-slate-500">{{ __('ui.per_day') }}</span></div>
                                    </div>
                                    <span class="px-4 py-2.5 text-xs font-bold bg-sky-500 group-hover:bg-sky-600 text-white rounded-xl shadow transition">Pesan</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- HOTEL POPULER (dari database) --}}
    @if ($featuredHotels->isNotEmpty())
        <section id="destinasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-2">
                <div>
                    <span class="text-xs font-black text-sky-600 uppercase tracking-widest">Katalog Akomodasi</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-1">Pilihan Hotel Populer</h2>
                </div>
                <a href="{{ route('hotels.search') }}" class="text-sm font-bold text-sky-600 hover:text-sky-700 inline-flex items-center gap-2">
                    Lihat Semua Hotel <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($featuredHotels as $hotel)
                    @php
                        $fallback = $unsplash($hotelFallbacks[$loop->index % count($hotelFallbacks)]);
                        $cover    = $hotel->coverUrl() ?: $fallback;
                    @endphp
                    <a href="{{ route('hotels.show', $hotel) . '?' . http_build_query($hotelParams) }}"
                       class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col group">
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
        </section>
    @endif

    {{-- JAMINAN LAYANAN --}}
    <section class="bg-sky-50/70 py-24 border-y border-sky-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-4 py-1.5 bg-sky-100 text-sky-800 text-xs font-black rounded-full uppercase tracking-wider inline-flex items-center gap-1.5 mb-3">
                    <i class="fa-solid fa-shield"></i> Jaminan Layanan
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900">SagaraSafe™: Bebas Cemas Selama Berlibur</h2>
                <p class="text-slate-500 text-sm mt-2">Perlindungan reservasi kamar & sewa kendaraan untuk kenyamanan maksimal Anda.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ([
                    ['fa-calendar-xmark', 'Gratis Pembatalan / Reschedule', 'Fleksibilitas ubah tanggal menginap atau jadwal sewa mobil tanpa potongan tersembunyi.'],
                    ['fa-car-on', 'Armada Bersih & Terawat', 'Setiap unit mobil selalu diservis berkala, bersih, dan siap langsung digunakan.'],
                    ['fa-handshake-angle', 'Mitra Terverifikasi', 'Bermitra langsung dengan pengelola hotel & rental terpercaya untuk garansi pasti dapat kamar & unit.'],
                ] as [$icon, $title, $desc])
                    <div class="bg-white p-8 rounded-3xl border border-sky-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-sky-500 text-white flex items-center justify-center text-2xl mb-6 shadow-md shadow-sky-500/20">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-2">{{ $title }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection