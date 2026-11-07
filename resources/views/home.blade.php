@php
    $authUser = auth()->user();

    $useDashboard = $authUser && (
        request()->routeIs('admin.*', 'partner.*')
        || (request()->routeIs('corporate.*') && $authUser->companyUser?->isFinance())
    );
@endphp
@extends($useDashboard ? 'layouts.dashboard' : 'layouts.storefront')

@section('content')

    {{-- HERO WITH BACKGROUND GAMBAR UTUH MEMBUNGKUS SEARCH BAR --}}
    <section class="relative bg-slate-900 text-white min-h-[640px] pt-24 pb-16 px-4 sm:px-6 lg:px-8 flex flex-col justify-between overflow-hidden">
        <!-- Background Gambar Alam (Full Mencakup Layar Atas & Form Search) -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80" 
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
                @include('partials.search-widget')
            </div>
        </div>
    </section>

    {{-- KETERSEDIAAN KOTA FAVORIT --}}
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
                @foreach ([
                    ['Labuan Bajo', 'Nusa Tenggara Timur', 'Kamar Tersedia', '45+ Hotel', 'Bisa Rental Lepas Kunci', 'Sopir Lokal Ready', 'Rating 4.9'],
                    ['Bali & Penida', 'Provinsi Bali', 'Sangat Laris', '120+ Resort', 'Unit Matic Melimpah', 'Antar-Jemput Bandara', 'Rating 4.8'],
                    ['Yogyakarta', 'D.I. Yogyakarta', 'Tersedia Banyak', '80+ Homestay', 'City Car & SUV', 'Unit Bersih & Terawat', 'Rating 4.9'],
                    ['Manado', 'Sulawesi Utara', 'Kamar Tersedia', '30+ Hotel', 'Rental Mobil Lepas Kunci', 'Layanan 24 Jam', 'Rating 4.7'],
                ] as [$name, $area, $status, $height, $cond, $wind, $vis])
                    <div class="bg-slate-50 hover:bg-sky-50/60 p-5 rounded-2xl border border-slate-200/80 hover:border-sky-300 transition duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-sm group-hover:text-sky-600 transition">{{ $name }}</h3>
                                    <p class="text-[11px] text-slate-500 font-medium">{{ $area }}</p>
                                </div>
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-extrabold rounded-full whitespace-nowrap">{{ $status }}</span>
                            </div>
                            <div class="flex items-baseline gap-2 my-3">
                                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $height }}</span>
                            </div>
                            <p class="text-xs font-semibold text-sky-600 flex items-center gap-1 mb-2">
                                <i class="fa-solid fa-car-side"></i> {{ $cond }}
                            </p>
                        </div>
                        <div class="flex justify-between text-[11px] font-medium text-slate-500 pt-3 border-t border-slate-200/60">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-shield text-slate-400"></i> {{ $wind }}</span>
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-star text-amber-400"></i> {{ $vis }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FLASH SALE BANNER (SATU WARNA SOLID SLATE-900 ELEGAN) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        <div class="bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-2xl relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 relative z-10">
                <div>
                    <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] uppercase tracking-widest font-black px-3.5 py-1.5 rounded-full inline-block mb-3">Penawaran Terbatas</span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white">Promo Bundling & Menginap</h2>
                    <p class="text-slate-400 text-xs sm:text-sm mt-1">Diskon spesial pemesanan hari ini. Berakhir dalam:</p>
                </div>
                <div class="flex items-center gap-3 bg-slate-800/90 px-5 py-3 rounded-2xl border border-slate-700 self-start sm:self-auto shadow-inner">
                    <i class="fa-regular fa-clock text-amber-400 text-xl animate-pulse"></i>
                    <div class="flex gap-1.5 font-mono font-black text-base text-white">
                        <span id="hours" class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-700">02</span>:
                        <span id="minutes" class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-700">47</span>:
                        <span id="seconds" class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-700">18</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                @foreach ([
                    ['photo-1544551763-46a013bb70d5', 'Diskon 40%', 'Rental Mobil', 'Innova Zenix / Avanza + Driver', 'AC dingin, unit bersih, gratis antar jemput bandara.', 'Rp 580.000', 'Rp 350.000', '/hari'],
                    ['photo-1540555700478-4be289fbecef', 'Hemat Rp 800rb', 'Resort & Villa', 'Ocean View Suite 3D2N', 'Fasilitas sarapan gratis & akses kolam renang.', 'Rp 2.750.000', 'Rp 1.950.000', '/malam'],
                    ['photo-1582967788606-a171c1080cb0', 'Diskon 30%', 'Paket Combo', 'Hotel Bintang 4 + Car Rental', 'Satu paket praktis menginap dan mobil 24 jam.', 'Rp 1.950.000', 'Rp 1.350.000', '/paket'],
                ] as [$img, $badge, $tag, $title, $desc, $old, $new, $unit])
                    <div class="bg-white text-slate-800 rounded-2xl overflow-hidden shadow-lg group hover:-translate-y-2 transition duration-300 flex flex-col justify-between">
                        <div>
                            <div class="relative h-48 overflow-hidden bg-slate-200">
                                <img src="https://images.unsplash.com/{{ $img }}?auto=format&fit=crop&w=600&q=80" alt="{{ $title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                <span class="absolute top-3 left-3 bg-amber-500 text-slate-950 text-xs font-black px-3 py-1 rounded-lg shadow-md">{{ $badge }}</span>
                                <span class="absolute bottom-3 left-3 bg-slate-900/90 text-white font-medium text-[11px] px-2.5 py-1 rounded-md backdrop-blur-sm">{{ $tag }}</span>
                            </div>
                            <div class="p-5">
                                <h3 class="font-extrabold text-slate-900 text-base mb-1.5 leading-snug">{{ $title }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $desc }}</p>
                            </div>
                        </div>
                        <div class="px-5 pb-5 pt-0">
                            <div class="flex items-end justify-between pt-3 border-t border-slate-100">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 line-through">{{ $old }}</span>
                                    <div class="text-lg font-black text-slate-900">{{ $new }} <span class="text-[11px] font-medium text-slate-500">{{ $unit }}</span></div>
                                </div>
                                <a href="#booking" class="px-4 py-2.5 text-xs font-bold bg-sky-500 hover:bg-sky-600 text-white rounded-xl shadow transition">Pesan</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- DESTINASI POPULER --}}
    <section id="destinasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-2">
            <div>
                <span class="text-xs font-black text-sky-600 uppercase tracking-widest">Katalog Akomodasi & Transportasi</span>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-1">Pilihan Destinasi & Penginapan Populer</h2>
            </div>
            <p class="text-slate-500 text-sm">Dikurasi berdasarkan kenyamanan hotel dan kebersihan armada.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ([
                ['photo-1516690561799-46d8f74f9abf', 'Papua Barat Daya', 'Raja Ampat Resort & Transport', 'Resort tepi pantai dengan pilihan sewa kendaraan penjemputan.', '4.95', 'Rp 4.500.000', 'Raja Ampat'],
                ['photo-1507525428034-b723cf961d3e', 'Kalimantan Timur', 'Derawan Eco-Lodge & Car', 'Penginapan kayu di atas laut dan armada antar-jemput bandara Berau.', '4.91', 'Rp 2.450.000', 'Derawan'],
                ['photo-1518509562904-e7ef99cdcc86', 'Nusa Tenggara Timur', 'Labuan Bajo Hotel & Car Rental', 'Penginapan pemandangan laut lengkap dengan sewa mobil area kota.', '4.98', 'Rp 1.950.000', 'Labuan Bajo'],
                ['photo-1544551763-46a013bb70d5', 'Maluku Tengah', 'Banda Neira Hotel & Transport', 'Hotel bersejarah dekat pelabuhan serta layanan kendaraan trip darat.', '4.96', 'Rp 3.750.000', 'Banda Neira'],
                ['photo-1506929562872-bb421503ef21', 'Jawa Tengah', 'Karimunjawa Cottage & Motor', 'Cottage pantai dan sewa kendaraan operasional selama liburan.', '4.88', 'Rp 750.000', 'Karimunjawa'],
                ['photo-1590523741831-ab7e8b8f9c7f', 'DKI Jakarta', 'Jakarta Luxury Hotel & Car', 'Hotel bintang 5 pusat kota + rental mobil mewah lepas kunci.', '4.82', 'Rp 450.000', 'Kepulauan Seribu'],
            ] as [$img, $prov, $name, $desc, $rating, $price, $city])
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="relative h-60 overflow-hidden">
                            <img src="https://images.unsplash.com/{{ $img }}?auto=format&fit=crop&w=700&q=80" alt="{{ $name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow">
                                <i class="fa-solid fa-star text-amber-400"></i> {{ $rating }}
                            </div>
                        </div>
                        <div class="p-6">
                            <span class="text-[11px] font-black text-sky-600 uppercase tracking-wider">{{ $prov }}</span>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 mb-2 group-hover:text-sky-600 transition">{{ $name }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-bold">Mulai Dari</span>
                                <span class="text-lg font-black text-slate-900">{{ $price }}</span>
                                <span class="text-xs text-slate-500">/malam</span>
                            </div>
                            <a href="{{ route('hotels.search', ['city' => $city, 'check_in' => today()->addDay()->toDateString(), 'check_out' => today()->addDays(2)->toDateString(), 'guests' => 2]) }}"
                               class="px-4 py-2 text-xs font-bold border border-slate-300 text-slate-700 hover:bg-sky-500 hover:border-sky-500 hover:text-white rounded-xl transition flex items-center gap-1">
                                Cari Hotel <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

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

@push('scripts')
<script>
    let totalSeconds = (2 * 3600) + (47 * 60) + 18;
    const pad = n => String(n).padStart(2, '0');

    setInterval(() => {
        if (totalSeconds <= 0) totalSeconds = 3 * 3600;
        totalSeconds--;
        document.getElementById('hours').innerText = pad(Math.floor(totalSeconds / 3600));
        document.getElementById('minutes').innerText = pad(Math.floor((totalSeconds % 3600) / 60));
        document.getElementById('seconds').innerText = pad(totalSeconds % 60);
    }, 1000);
</script>
@endpush