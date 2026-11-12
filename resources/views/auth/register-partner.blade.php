@extends('layouts.storefront')

@section('content')
    <div class="bg-slate-50 text-slate-800 min-h-screen font-sans antialiased">

        {{-- 1. HERO BANNER --}}
        <section class="relative min-h-screen flex items-center pt-28 sm:pt-32 pb-20 bg-neutral-800 text-white overflow-hidden">

            {{-- Background Image + Overlay (lebih terang, netral) --}}
            <div class="absolute inset-0 z-0 select-none">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1920&q=80"
                     alt="Kemitraan bisnis SekalaGo"
                     class="w-full h-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/35 to-black/5"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
            </div>

            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-2xl space-y-6 text-left">
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15] drop-shadow-md">
                        {{ __('ui.partner_why_title') }}
                    </h1>

                    <p class="text-white/90 text-base sm:text-lg font-normal leading-relaxed max-w-xl drop-shadow-md">
                        {{ __('ui.partner_why_hint') }}
                    </p>

                    <div class="pt-2 flex flex-wrap gap-4 items-center">
                        <a href="#register-section" class="px-7 py-3.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-extrabold rounded-xl shadow-lg shadow-black/20 transition duration-300 flex items-center gap-2.5 text-sm">
                            <i class="fa-solid fa-pen-to-square"></i> Daftar Mitra Sekarang
                        </a>
                        <a href="#about-partnership" class="px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl border border-white/30 backdrop-blur-md transition duration-300 flex items-center gap-2 text-sm">
                            Pelajari Kemitraan <i class="fa-solid fa-arrow-down text-xs text-sky-300"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. SEKSI PENGENALAN PROGRAM MITRA --}}
        <section id="about-partnership" class="bg-white py-16 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    {{-- Deskripsi Ringkas --}}
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block">Tentang Kemitraan</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-snug">
                            Tumbuh Bersama Jaringan Akomodasi & Transportasi SekalaGo
                        </h2>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                            SekalaGo menghubungkan pengelola hotel, villa, dan penyedia sewa kendaraan langsung dengan wisatawan serta pelanggan korporasi. Melalui platform terintegrasi, kami membantu mengoptimalkan tingkat okupansi kamar dan penggunaan armada secara efisien.
                        </p>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                            Sistem manajemen terpadu memudahkan pemantauan reservasi, penyesuaian tarif dinamis, serta pencairan dana secara aman dan transparan tanpa proses administrasi yang rumit.
                        </p>
                    </div>

                    {{-- Grid Key Highlights --}}
                    <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 text-base border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-900">B2B & B2C</h3>
                            <p class="text-xs text-slate-500 mt-1">Jangkauan pasar wisatawan & pelanggan korporasi.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 text-base border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-900">Real-Time</h3>
                            <p class="text-xs text-slate-500 mt-1">Laporan pemesanan & manajemen unit instan.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 text-base border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-900">Transparan</h3>
                            <p class="text-xs text-slate-500 mt-1">Pencairan dana jelas & pembayaran tergaransi.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 text-base border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-900">Dukungan</h3>
                            <p class="text-xs text-slate-500 mt-1">Tim operasional siap membantu kemitraan Anda.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 3. SEKSI KEUNTUNGAN MITRA --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-2">Nilai Tambah</span>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900">Keunggulan Bergabung Sebagai Mitra</h2>
                <p class="text-slate-500 text-sm mt-2">Dapatkan berbagai kemudahan fasilitas operasional dan dukungan ekosistem digital SekalaGo.</p>
            </div>

            @php
                $icons = ['fa-store', 'fa-briefcase', 'fa-bed', 'fa-receipt', 'fa-language', 'fa-file-signature'];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (__('ui.partner_benefits') as $i => $benefit)
                    <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-400 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-4 border border-sky-100 transition duration-300 shadow-sm">
                                <i class="fa-solid {{ $icons[$i] ?? 'fa-star' }} text-lg"></i>
                            </div>
                            <h3 class="font-bold text-base mb-2 text-slate-900 group-hover:text-sky-600 transition leading-snug">{{ $benefit['title'] }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $benefit['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 4. SEKSI LANGKAH BERGABUNG & FAQ --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 border-t border-slate-200/60">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

                {{-- Steps Timeline Style --}}
                <div>
                    <div class="mb-6">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-1">Alur Pendaftaran</span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-list-check text-sky-600"></i>
                            {{ __('ui.partner_steps_title') }}
                        </h2>
                    </div>

                    <div class="relative space-y-4 pl-2">
                        @foreach (__('ui.partner_steps') as $i => $step)
                            <div class="relative flex items-start gap-4 rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm hover:border-sky-300 transition duration-200">
                                <span class="w-8 h-8 shrink-0 rounded-xl bg-sky-500 text-white font-black text-xs flex items-center justify-center shadow-md shadow-sky-500/20">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h3 class="font-bold text-sm text-slate-900 leading-tight">{{ $step['title'] }}</h3>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Accordion FAQ (dinamis dari database) --}}
                <div>
                    <div class="mb-6">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-1">Pertanyaan Umum</span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-question text-sky-600"></i>
                            {{ __('ui.partner_faq_title') }}
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse ($faqs as $faq)
                            @php $t = $faq->translation(); @endphp

                            @if ($t)
                                <details class="rounded-2xl bg-white border border-slate-200/80 px-5 py-4 group shadow-sm transition duration-200 hover:border-sky-300">
                                    <summary class="font-bold text-sm cursor-pointer list-none flex justify-between items-center text-slate-900 select-none">
                                        <span>{{ $t->question }}</span>
                                        <span class="w-6 h-6 rounded-full bg-slate-100 group-open:bg-sky-50 group-open:text-sky-600 flex items-center justify-center text-slate-400 transition-all duration-200 shrink-0 ml-2">
                                            <i class="fa-solid fa-chevron-down text-[10px] group-open:rotate-180 transition-transform"></i>
                                        </span>
                                    </summary>
                                    <p class="text-xs text-slate-500 mt-3 leading-relaxed border-t border-slate-100 pt-3 whitespace-pre-line">
                                        {{ $t->answer }}
                                    </p>
                                </details>
                            @endif
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada pertanyaan umum.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </section>

        {{-- 5. FORMULIR PENDAFTARAN MITRA --}}
        <section id="register-section" class="relative overflow-hidden bg-neutral-800 py-16 sm:py-24">

            {{-- Background sama seperti hero --}}
            <div class="absolute inset-0 z-0 select-none">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1920&q=80"
                     alt="" class="w-full h-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/40 to-black/20"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                    {{-- Kiri: teks sambutan --}}
                    <div class="lg:col-span-5 space-y-6 text-white">
                        <div>
                            <span class="text-xs font-black text-sky-300 uppercase tracking-widest block mb-2">Langkah Terakhir</span>
                            <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-[1.15] drop-shadow-md">
                                Siap Mengembangkan Usaha Bersama SekalaGo?
                            </h2>
                        </div>

                        <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md">
                            Isi formulir pendaftaran untuk mendaftarkan properti akomodasi atau armada kendaraan Anda. Tim operasional kami akan memverifikasi data Anda dalam waktu 1x24 jam.
                        </p>

                        <div class="space-y-4 pt-4 border-t border-white/15">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-white/10 text-sky-300 flex items-center justify-center shrink-0 border border-white/25 backdrop-blur-md">
                                    <i class="fa-solid fa-bolt text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-white">Proses Cepat & Langsung Aktif</h3>
                                    <p class="text-xs text-white/75 mt-0.5">Akun mitra langsung siap dikelola setelah verifikasi selesai.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-white/10 text-sky-300 flex items-center justify-center shrink-0 border border-white/25 backdrop-blur-md">
                                    <i class="fa-solid fa-lock text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-white">Kerahasiaan Data Terjamin</h3>
                                    <p class="text-xs text-white/75 mt-0.5">Seluruh informasi legalitas dan identitas disimpan secara aman.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-black/30 backdrop-blur-md border border-white/15 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-info text-sky-300 text-base"></i>
                                <span class="text-xs font-semibold text-white/90">Butuh bantuan pendaftaran?</span>
                            </div>
                            <a href="https://wa.me/" target="_blank" class="text-xs font-extrabold text-sky-300 hover:text-sky-200 hover:underline flex items-center gap-1">
                                Hubungi Kami <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Kanan: card putih berisi form --}}
                    <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/30 border border-white/20">
                        <div class="mb-6">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ __('ui.register_partner') }}</h3>
                            <p class="text-slate-500 text-xs mt-1">{{ __('ui.register_partner_hint') }}</p>
                        </div>

                        <form method="POST" action="{{ route('register.partner.store') }}" class="space-y-4">
                            @csrf

                            @if ($errors->any())
                                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs space-y-1">
                                    <p class="font-bold">Terjadi kesalahan pada input data:</p>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php
                                $box = 'bg-slate-50 p-3.5 rounded-xl border border-slate-200/90 focus-within:bg-white focus-within:border-sky-500 focus-within:ring-4 focus-within:ring-sky-500/10 transition duration-200';
                                $label = 'block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1';
                                $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none placeholder-slate-400';
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-building text-sky-600 mr-1"></i> {{ __('ui.company_name') }}</label>
                                    <input name="company_name" value="{{ old('company_name') }}" placeholder="PT / CV / Nama Usaha" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-id-card text-sky-600 mr-1"></i> {{ __('ui.business_license_no') }}</label>
                                    <input name="business_license_no" value="{{ old('business_license_no') }}" placeholder="NIB / Nomor Izin Usaha" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-building-columns text-sky-600 mr-1"></i> {{ __('ui.bank_account_no') }}</label>
                                    <input name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="No. Rekening Bank" class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-phone text-sky-600 mr-1"></i> {{ __('ui.phone') }}</label>
                                    <input name="phone_number" value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                                </div>

                                <div class="{{ $box }} sm:col-span-2">
                                    <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sky-600 mr-1"></i> {{ __('ui.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@perusahaan.com" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.password') }}</label>
                                    <input type="password" name="password" placeholder="••••••••" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.password_confirm') }}</label>
                                    <input type="password" name="password_confirmation" placeholder="••••••••" required class="{{ $input }}">
                                </div>
                            </div>

                            <button type="submit" class="w-full py-4 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-black rounded-xl shadow-lg shadow-sky-500/25 hover:shadow-sky-500/35 transition duration-300 flex items-center justify-center gap-2 mt-6 text-sm">
                                <i class="fa-solid fa-handshake"></i> {{ __('ui.register') }}
                            </button>
                        </form>

                        <p class="text-center text-xs text-slate-500 mt-6">
                            Sudah punya akun mitra? <a href="{{ route('login') }}" class="font-extrabold text-sky-600 hover:text-sky-700 hover:underline">{{ __('ui.login') }}</a>
                        </p>
                    </div>

                </div>
            </div>
        </section>

    </div>
@endsection