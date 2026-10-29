@extends('layouts.app')

@section('content')

    {{-- HERO + SEARCH --}}
    <section class="hero-bg text-white relative pt-12 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#ffffff" d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,149.3C672,149,768,203,864,224C960,245,1056,235,1152,202.7C1248,171,1344,117,1392,90.7L1440,64L1440,320L0,320Z"></path>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-sky-300/30 text-sky-200 text-xs font-medium mb-6">
                <i class="fa-solid fa-shield-halved text-sagara-teal"></i> SagaraSafe™ Certified • Platform Wisata Bahari Resmi Indonesia
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-4xl mx-auto mb-4">
                {{ __('ui.hero_title') }}
            </h1>

            <p class="text-base sm:text-lg text-sky-100 max-w-2xl mx-auto mb-8 font-light leading-relaxed">
                Temukan penginapan, tiket penyeberangan, hingga paket trip eksklusif di perairan Indonesia.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 max-w-3xl mx-auto mb-10 text-left">
                @foreach ([
                    ['fa-umbrella-beach', '17.000+', 'Pulau Eksotik Terjangkau'],
                    ['fa-anchor', '484+', 'Pelabuhan & Dermaga'],
                    ['fa-circle-check', '100%', 'Tersertifikasi & Terlindungi'],
                ] as [$icon, $num, $label])
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-sagara-teal/20 flex items-center justify-center text-sagara-teal text-xl">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div>
                            <div class="text-xl sm:text-2xl font-extrabold">{{ $num }}</div>
                            <div class="text-xs text-sky-200">{{ $label }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="booking" class="glass-card rounded-3xl p-4 sm:p-6 text-slate-800 shadow-2xl max-w-5xl mx-auto border border-white/60">
                @include('partials.search-form')
            </div>
        </div>
    </section>

    {{-- RADAR OMBAK --}}
    <section id="radar" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 mb-16">
        <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-100">
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sagara-blue font-bold text-xs uppercase tracking-wider">
                    <i class="fa-solid fa-circle-dot text-emerald-500 animate-pulse"></i> Real-time Marine Data BMKG
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">Radar Cuaca & Kondisi Ombak Terkini</h2>
                <p class="text-slate-500 text-xs sm:text-sm">Pantau keamanan dan ketinggian ombak sebelum reservasi perjalanan Anda.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['Labuan Bajo', 'Taman Nasional Komodo, NTT', 'Sangat Aman', '0.5 m', 'Ombak Tenang', '8 knot (Barat)', '10 km'],
                    ['Sanur ➔ Penida', 'Selat Badung, Bali', 'Lancar', '0.8 m', 'Moderat Aman', '12 knot (Barat)', '12 km'],
                    ['Derawan & Maratua', 'Laut Sulawesi, Kaltim', 'Sangat Tenang', '0.3 m', 'Ideal Diving', '5 knot (Utara)', '15 km'],
                    ['Bunaken Marine Park', 'Teluk Manado, Sulut', 'Sangat Aman', '0.4 m', 'Jernih', '6 knot (Timur)', '14 km'],
                ] as [$name, $area, $status, $height, $cond, $wind, $vis])
                    <div class="bg-slate-50 hover:bg-sky-50/50 p-4 rounded-2xl border border-slate-200/80 transition">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">{{ $name }}</h3>
                                <p class="text-[11px] text-slate-500">{{ $area }}</p>
                            </div>
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full whitespace-nowrap">{{ $status }}</span>
                        </div>
                        <div class="flex items-baseline gap-2 my-2">
                            <span class="text-2xl font-black text-slate-900">{{ $height }}</span>
                            <span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-water"></i> {{ $cond }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-200/60">
                            <span><i class="fa-solid fa-wind text-slate-400"></i> {{ $wind }}</span>
                            <span><i class="fa-solid fa-eye text-slate-400"></i> {{ $vis }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FLASH SALE --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
        <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <span class="bg-sagara-coral text-[10px] uppercase tracking-widest font-extrabold px-3 py-1 rounded-full inline-block mb-2">Penawaran Terbatas</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold">Promo Gelombang Kejutan</h2>
                    <p class="text-sky-200 text-xs sm:text-sm mt-1">Diskon hingga 45%. Berakhir dalam:</p>
                </div>
                <div class="flex items-center gap-2 bg-sagara-dark/80 px-4 py-2.5 rounded-2xl border border-white/10 self-start sm:self-auto">
                    <i class="fa-regular fa-clock text-sagara-teal text-xl"></i>
                    <div class="flex gap-1.5 font-mono font-bold text-base">
                        <span id="hours" class="bg-white/10 px-2 py-1 rounded-lg">02</span>:
                        <span id="minutes" class="bg-white/10 px-2 py-1 rounded-lg">47</span>:
                        <span id="seconds" class="bg-white/10 px-2 py-1 rounded-lg">18</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['photo-1544551763-46a013bb70d5', 'Diskon 40%', 'Fast Boat Express', 'Bali ➔ Gili & Lombok', 'Fastboat double-turbo dengan deck AC VIP.', 'Rp 580.000', 'Rp 350.000', '/pax'],
                    ['photo-1540555700478-4be289fbecef', 'Hemat Rp 800rb', 'Water Villa 3D2N', 'Maratua Eco-Resort', 'Menginap di atas laguna bening kristal.', 'Rp 2.750.000', 'Rp 1.950.000', '/malam'],
                    ['photo-1582967788606-a171c1080cb0', 'Diskon 30%', 'Eco-Diving Pass', 'Takabonerate & Wakatobi', 'Eksplorasi atol dengan instruktur SSI/PADI.', 'Rp 1.950.000', 'Rp 1.350.000', '/pax'],
                ] as [$img, $badge, $tag, $title, $desc, $old, $new, $unit])
                    <div class="bg-white text-slate-800 rounded-2xl overflow-hidden shadow-lg group hover:-translate-y-1.5 transition duration-300">
                        <div class="relative h-48 overflow-hidden bg-slate-200">
                            <img src="https://images.unsplash.com/{{ $img }}?auto=format&fit=crop&w=600&q=80" alt="{{ $title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            <span class="absolute top-3 left-3 bg-sagara-coral text-white text-xs font-extrabold px-2.5 py-1 rounded-lg shadow">{{ $badge }}</span>
                            <span class="absolute bottom-3 left-3 bg-sagara-dark/80 text-white text-[11px] px-2 py-0.5 rounded">{{ $tag }}</span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-900 mb-1">{{ $title }}</h3>
                            <p class="text-xs text-slate-500 mb-4">{{ $desc }}</p>
                            <div class="flex items-end justify-between">
                                <div>
                                    <span class="text-xs text-slate-400 line-through">{{ $old }}</span>
                                    <div class="text-xl font-black text-sagara-blue">{{ $new }} <span class="text-xs font-normal text-slate-500">{{ $unit }}</span></div>
                                </div>
                                <a href="#booking" class="px-4 py-2 text-xs font-bold bg-sagara-blue hover:bg-sagara-navy text-white rounded-xl transition">Pesan</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- DESTINASI --}}
    <section id="destinasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        <div class="mb-8">
            <span class="text-xs font-bold text-sagara-blue uppercase tracking-widest">Katalog Bahari Archipelago</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-1">Koleksi Destinasi Kepulauan Pilihan</h2>
            <p class="text-slate-500 text-sm mt-1">Dikurasi dengan standar keamanan bahari tinggi.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ([
                ['photo-1516690561799-46d8f74f9abf', 'Papua Barat Daya', 'Raja Ampat (Misool & Wayag)', 'Karst Wayag, pari manta, dan resort terapung di atas laut.', '4.95', 'Rp 4.500.000', 'Raja Ampat'],
                ['photo-1507525428034-b723cf961d3e', 'Kalimantan Timur', 'Derawan & Kakaban', 'Berenang bersama ubur-ubur tanpa sengat dan menyelam di Sangalaki.', '4.91', 'Rp 2.450.000', 'Derawan'],
                ['photo-1518509562904-e7ef99cdcc86', 'Nusa Tenggara Timur', 'Labuan Bajo & TN Komodo', 'Pelayaran Phinisi 3D2N, Pulau Padar, Pink Beach, dan komodo.', '4.98', 'Rp 1.950.000', 'Labuan Bajo'],
                ['photo-1544551763-46a013bb70d5', 'Maluku Tengah', 'Banda Neira', 'Jejak sejarah rempah dan hiu martil di laut dalam Banda.', '4.96', 'Rp 3.750.000', 'Banda Neira'],
                ['photo-1506929562872-bb421503ef21', 'Jawa Tengah', 'Karimunjawa', 'Snorkeling air dangkal, konservasi hiu, dan pantai pasir putih.', '4.88', 'Rp 750.000', 'Karimunjawa'],
                ['photo-1590523741831-ab7e8b8f9c7f', 'DKI Jakarta', 'Pulau Macan & Pulau Pari', 'Pelarian singkat 90 menit dari Marina Ancol.', '4.82', 'Rp 450.000', 'Kepulauan Seribu'],
            ] as [$img, $prov, $name, $desc, $rating, $price, $city])
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition duration-300 flex flex-col">
                    <div class="relative h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/{{ $img }}?auto=format&fit=crop&w=700&q=80" alt="{{ $name }}" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                        <div class="absolute top-3 right-3 bg-white/90 text-slate-800 text-xs font-bold px-2 py-1 rounded-lg flex items-center gap-1 shadow">
                            <i class="fa-solid fa-star text-amber-400"></i> {{ $rating }}
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">{{ $prov }}</span>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 mb-2">{{ $name }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $desc }}</p>
                        </div>
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Mulai Dari</span>
                                <span class="text-lg font-black text-slate-900">{{ $price }}</span>
                                <span class="text-xs text-slate-500">/pax</span>
                            </div>
                            <a href="{{ route('hotels.search', ['city' => $city, 'check_in' => today()->addDay()->toDateString(), 'check_out' => today()->addDays(2)->toDateString(), 'guests' => 2]) }}"
                               class="px-4 py-2 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-xl transition">
                                Cari Hotel <i class="fa-solid fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- SAGARASAFE --}}
    <section class="bg-slate-100/70 py-20 border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="px-3 py-1 bg-sagara-blue/10 text-sagara-blue text-xs font-extrabold rounded-full uppercase tracking-wider"><i class="fa-solid fa-shield"></i> Navigasi Aman</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-2">SagaraSafe™: Garansi Bebas Cemas</h2>
                <p class="text-slate-500 text-sm mt-2">Tiga pilar proteksi untuk ketenangan penuh Anda.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ([
                    ['fa-cloud-showers-heavy', 'Fleksibilitas Cuaca Buruk', 'Jika BMKG mengeluarkan peringatan gelombang ekstrem, tersedia opsi reschedule atau refund.'],
                    ['fa-file-contract', 'Asuransi Terpadu', 'Setiap tiket penumpang terlindungi jaminan asuransi perjalanan laut resmi.'],
                    ['fa-award', 'Operator Tersertifikasi', 'Hanya bermitra dengan operator terverifikasi: kelaiklautan, life-jacket lengkap, nakhoda berlisensi.'],
                ] as [$icon, $title, $desc])
                    <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm hover:shadow-md transition">
                        <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sagara-blue flex items-center justify-center text-2xl mb-6">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $title }}</h3>
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