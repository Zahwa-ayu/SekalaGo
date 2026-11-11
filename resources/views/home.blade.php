@php
    $authUser = auth()->user();

    $useDashboard = $authUser && (
        request()->routeIs('admin.*', 'partner.*')
        || (request()->routeIs('corporate.*') && $authUser->companyUser?->isFinance())
    );

    // Terjemahan sesuai bahasa aktif dari relasi `translations` yang sudah di-load (fallback: terjemahan pertama).
    $tr = function ($model) {
        $locale = app()->getLocale();
        return $model->translations->first(
            fn ($t) => in_array($locale, [$t->locale ?? null, $t->lang ?? null, $t->language ?? null], true)
        ) ?? $model->translations->first();
    };

    // Foto pertama model; sesuaikan nama kolom foto jika berbeda.
    $photoUrl = function ($model, string $fallback) {
        $p = $model->photos->first();
        if (! $p) return $fallback;

        // Model Photo punya method url() — pakai itu kalau ada.
        if (method_exists($p, 'url')) {
            return $p->url() ?: $fallback;
        }

        $attrs = $p->getAttributes();
        $path  = $attrs['path'] ?? $attrs['file_path'] ?? null;
        return $path ? asset('storage/' . ltrim($path, '/')) : $fallback;
    };
    $fallbackImg = 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=700&q=80';
@endphp
@extends($useDashboard ? 'layouts.dashboard' : 'layouts.storefront')

@section('content')

    {{-- HERO WITH BACKGROUND GAMBAR UTUH MEMBUNGKUS SEARCH BAR --}}
    <section class="relative bg-slate-900 text-white min-h-[640px] pt-24 pb-16 px-4 sm:px-6 lg:px-8 flex flex-col justify-between overflow-hidden">
        <!-- Background Gambar Alam (Full Mencakup Layar Atas & Form Search) -->
        <div class="absolute inset-0 z-0">
            <img src="{{ \App\Models\SiteBanner::urlFor('home') }}"
                onerror="this.onerror=null;this.src='{{ \App\Models\SiteBanner::defaultUrl('home') }}'"
                alt="Background Alam Travel"
                class="w-full h-full object-cover object-center scale-105">
            <!-- Overlay Gradien Lembut -->
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-slate-950/75"></div>
        </div>

        <!-- Judul Hero (Di dalam Foto Background) -->
        <div class="max-w-7xl mx-auto relative z-10 text-center pt-6 mb-8">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl mx-auto mb-3 text-white drop-shadow-md">
                {{ __('ui.hero_title') }}
            </h1>
            <p class="text-base sm:text-lg text-slate-100 max-w-xl mx-auto font-medium drop-shadow-sm">
                Temukan hotel impian & rental mobil terbaik untuk liburan Anda.
            </p>
        </div>

        <!-- Form Pencarian (Sepenuhnya Berada di Dalam Foto Background) -->
        <div class="max-w-6xl mx-auto w-full relative z-20 pb-4">
            <div id="booking" class="bg-white rounded-3xl p-5 sm:p-7 text-slate-800 shadow-2xl border border-slate-100/90 ring-1 ring-slate-900/5">
                {{-- Widget pencarian bertab (Hotel / Rental Mobil), berganti di tempat --}}
                @include('partials.search-widget')
            </div>
        </div>
    </section>

    {{-- HASIL PENCARIAN (tetap di Beranda) --}}
    @if (isset($hotelSearch) || isset($carSearch))
        <section id="results" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 scroll-mt-24">
            @isset($hotelSearch)
                <div data-results-for="hotels">
                    @include('partials.hotel-results', ['search' => $hotelSearch])
                </div>
            @endisset

            @isset($carSearch)
                <div data-results-for="cars">
                    @include('partials.car-results', ['search' => $carSearch, 'resultsRoute' => 'home', 'extra' => ['tab' => 'cars']])
                </div>
            @endisset
        </section>
    @endif

    {{-- KOTA FAVORIT (dari database: hotel & armada yang sudah disetujui) --}}
    @if ($cityStats->isNotEmpty())
        <section id="radar" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 mb-20">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-100">
                <div class="mb-6">
                    <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-1">Kota Tujuan</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">Kota Favorit Wisatawan</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($cityStats as $c)
                        <a href="{{ route('home', ['tab' => 'hotels', 'city' => $c->city, 'check_in' => today()->addDay()->toDateString(), 'check_out' => today()->addDays(2)->toDateString(), 'guests' => 2]) }}#results"
                           class="bg-slate-50 hover:bg-sky-50/60 p-5 rounded-2xl border border-slate-200/80 hover:border-sky-300 transition duration-300 flex flex-col justify-between group">
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-extrabold text-slate-900 text-sm group-hover:text-sky-600 transition flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-sky-500 text-xs"></i> {{ $c->city }}
                                    </h3>
                                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300 group-hover:text-sky-500 group-hover:translate-x-0.5 transition"></i>
                                </div>
                                <div class="my-3">
                                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $c->hotels }} Hotel</span>
                                </div>
                                @if ($c->vehicles > 0)
                                    <p class="text-xs font-semibold text-sky-600 flex items-center gap-1 mb-2">
                                        <i class="fa-solid fa-car-side"></i> {{ $c->vehicles }} Armada Rental
                                    </p>
                                @endif
                            </div>
                            <div class="flex justify-between items-end pt-3 border-t border-slate-200/60">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">Mulai Dari</span>
                                    <span class="text-sm font-black text-slate-900">{{ format_price($c->min_price, 'IDR') }}</span>
                                    <span class="text-[11px] text-slate-500">/malam</span>
                                </div>
                                @if ($c->avg_star > 0)
                                    <span class="flex items-center gap-1 text-[11px] font-bold text-slate-600"><i class="fa-solid fa-star text-amber-400"></i> {{ $c->avg_star }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ARMADA RENTAL PILIHAN (dari database: harga sewa harian terendah) --}}
    @if ($featuredVehicles->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <div class="bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 relative z-10">
                    <div>
                        <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] uppercase tracking-widest font-black px-3.5 py-1.5 rounded-full inline-block mb-3">Harga Terbaik</span>
                        <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white">Armada Rental Pilihan</h2>
                        <p class="text-slate-400 text-xs sm:text-sm mt-1">Unit dengan harga sewa harian terendah saat ini.</p>
                    </div>
                    <a href="{{ route('cars.home') }}" class="text-xs font-bold text-sky-300 hover:text-sky-200 flex items-center gap-1 self-start sm:self-auto">
                        Lihat Semua Armada <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    @foreach ($featuredVehicles as $vehicle)
                        @php
                            $vt = $tr($vehicle);
                            $vName = $vt->name ?? $vehicle->name;
                        @endphp
                        <div class="bg-white text-slate-800 rounded-2xl overflow-hidden shadow-lg group hover:-translate-y-2 transition duration-300 flex flex-col justify-between">
                            <div>
                                <div class="relative h-48 overflow-hidden bg-slate-200">
                                    <img src="{{ $photoUrl($vehicle, $fallbackImg) }}" alt="{{ $vName }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    @if ($vehicle->seats)
                                        <span class="absolute top-3 left-3 bg-amber-500 text-slate-950 text-xs font-black px-3 py-1 rounded-lg shadow-md">{{ $vehicle->seats }} Kursi</span>
                                    @endif
                                    <span class="absolute bottom-3 left-3 bg-slate-900/90 text-white font-medium text-[11px] px-2.5 py-1 rounded-md backdrop-blur-sm">{{ $vehicle->city }}</span>
                                </div>
                                <div class="p-5">
                                    <h3 class="font-extrabold text-slate-900 text-base mb-1.5 leading-snug">{{ $vName }}</h3>
                                    <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ \Illuminate\Support\Str::limit($vt->description ?? '', 90) }}</p>
                                </div>
                            </div>
                            <div class="px-5 pb-5 pt-0">
                                <div class="flex items-end justify-between pt-3 border-t border-slate-100">
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">Mulai Dari</span>
                                        <div class="text-lg font-black text-slate-900">{{ format_price($vehicle->price_per_day, 'IDR') }} <span class="text-[11px] font-medium text-slate-500">/hari</span></div>
                                    </div>
                                    <a href="{{ route('cars.show', $vehicle) }}" class="px-4 py-2.5 text-xs font-bold bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow transition">Lihat</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- HOTEL PILIHAN (dari database) --}}
    @if ($featuredHotels->isNotEmpty())
        <section id="destinasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-2">
                <div>
                    <span class="text-xs font-black text-sky-600 uppercase tracking-widest">Akomodasi Pilihan</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-1">Hotel Pilihan Untuk Liburan Anda</h2>
                </div>
                <a href="{{ route('hotels.search') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 self-start sm:self-auto">
                    Lihat Semua Hotel <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($featuredHotels as $hotel)
                    @php
                        $ht = $tr($hotel);
                        $hName = $ht->name ?? $hotel->name;
                    @endphp
                    <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="relative h-60 overflow-hidden bg-slate-200">
                                <img src="{{ $photoUrl($hotel, $fallbackImg) }}" alt="{{ $hName }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @if ($hotel->star_rating)
                                    <div class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow">
                                        <i class="fa-solid fa-star text-amber-400"></i> {{ $hotel->star_rating }}
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <span class="text-[11px] font-black text-sky-600 uppercase tracking-wider">{{ $hotel->city }}</span>
                                <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 mb-2 group-hover:text-sky-600 transition">{{ $hName }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">{{ \Illuminate\Support\Str::limit($ht->description ?? '', 110) }}</p>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">Mulai Dari</span>
                                    <span class="text-lg font-black text-slate-900">{{ format_price($hotel->min_price, 'IDR') }}</span>
                                    <span class="text-xs text-slate-500">/malam</span>
                                </div>
                                <a href="{{ route('hotels.show', $hotel) }}"
                                   class="px-4 py-2 text-xs font-bold border border-slate-300 text-slate-700 hover:bg-sky-500 hover:border-sky-500 hover:text-white rounded-xl transition flex items-center gap-1">
                                    Lihat Hotel <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
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
                    <div class="bg-white p-8 rounded-3xl border border-slate-200/80 hover:border-sky-400 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center text-2xl mb-6 border border-sky-100 shadow-sm transition duration-300">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-sky-600 transition mb-2">{{ $title }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection