@extends('layouts.storefront')

@section('content')
    <div class="bg-slate-50 text-slate-800 min-h-screen">
        
        {{-- 1. HERO BANNER (Gradasi Biru Malam / Deep Ocean Gradient) --}}
        <section class="relative pt-28 sm:pt-32 pb-16 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 text-white border-b border-slate-800/80 overflow-hidden">
            
            <!-- Ambient Glow Effect (Gradasi Ornamen Biru Cerah & Teal) -->
            <div class="absolute -top-20 -right-20 w-[500px] h-[500px] bg-sky-500/15 blur-[140px] pointer-events-none rounded-full"></div>
            <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] bg-indigo-600/10 blur-[130px] pointer-events-none rounded-full"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    {{-- Sisi Kiri: Teks & Action Button --}}
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-500/10 border border-sky-400/30 text-sky-300 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                            <i class="fa-solid fa-handshake-angle text-sky-400"></i> Program Kemitraan SekalaGo
                        </span>
                        
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15] drop-shadow-sm">
                            {{ __('ui.partner_why_title') }}
                        </h1>
                        
                        <p class="text-sky-100/80 text-base sm:text-lg font-normal leading-relaxed max-w-xl">
                            {{ __('ui.partner_why_hint') }}
                        </p>

                        {{-- Tombol Utama Menggunakan Warna bg-sky-500 --}}
                        <div class="pt-2 flex flex-wrap gap-4 items-center">
                            <a href="#register-section" class="px-7 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-extrabold rounded-xl shadow-lg shadow-sky-500/20 transition duration-300 flex items-center gap-2 text-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Daftar Mitra Sekarang
                            </a>
                            <a href="#about-partnership" class="px-6 py-3.5 bg-slate-800/80 hover:bg-slate-800 text-slate-200 font-bold rounded-xl border border-slate-700/80 backdrop-blur-sm transition duration-300 flex items-center gap-2 text-sm">
                                Pelajari Kemitraan <i class="fa-solid fa-arrow-down text-xs text-sky-400"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Sisi Kanan: Foto Banner Destinasi --}}
                    <div class="lg:col-span-5">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-white/10 group">
                            <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=900&q=80" 
                                 alt="Kemitraan SekalaGo" 
                                 class="w-full h-[360px] sm:h-[420px] object-cover group-hover:scale-105 transition duration-700">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent flex items-end p-6">
                                <div>
                                    <span class="text-[10px] uppercase font-bold tracking-widest text-sky-400 block mb-1">Akomodasi & Armada</span>
                                    <h4 class="text-lg font-black text-white leading-snug">Jangkau Lebih Banyak Wisatawan & Klien Korporasi</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. SEKSI PENGENALAN PROGRAM MITRA --}}
        <section id="about-partnership" class="bg-white py-14 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    {{-- Narasi Pengenalan --}}
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

                    {{-- Card Ringkasan Fitur --}}
                    <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 text-lg border border-sky-100">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <h4 class="text-base font-black text-slate-900">B2B & B2C</h4>
                            <p class="text-xs text-slate-500 mt-1">Jangkauan pasar wisatawan & pelanggan korporasi.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 text-lg border border-sky-100">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <h4 class="text-base font-black text-slate-900">Real-Time</h4>
                            <p class="text-xs text-slate-500 mt-1">Laporan pemesanan & manajemen unit instan.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 text-lg border border-sky-100">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h4 class="text-base font-black text-slate-900">Transparan</h4>
                            <p class="text-xs text-slate-500 mt-1">Pencairan dana jelas & pembayaran tergaransi.</p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 text-lg border border-sky-100">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <h4 class="text-base font-black text-slate-900">Dukungan</h4>
                            <p class="text-xs text-slate-500 mt-1">Tim operasional siap membantu kemitraan Anda.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 3. SEKSI KEUNTUNGAN MITRA --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-2">Nilai Tambah</span>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900">Keunggulan Bergabung Sebagai Mitra</h2>
                <p class="text-slate-500 text-sm mt-2">Dapatkan berbagai kemudahan fasilitas operasional dan dukungan ekosistem digital SekalaGo.</p>
            </div>

            @php
                $icons = ['fa-store', 'fa-briefcase', 'fa-bed', 'fa-receipt', 'fa-language', 'fa-file-signature'];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (__('ui.partner_benefits') as $i => $benefit)
                    <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-300 p-6 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-4 border border-sky-100 transition duration-300">
                                <i class="fa-solid {{ $icons[$i] ?? 'fa-star' }} text-base"></i>
                            </div>
                            <h3 class="font-bold text-base mb-2 text-slate-900 group-hover:text-sky-600 transition leading-snug">{{ $benefit['title'] }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $benefit['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 4. SEKSI LANGKAH BERGABUNG & FAQ --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-6 border-t border-slate-200/60">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start">
                
                {{-- Langkah Bergabung --}}
                <div>
                    <div class="mb-5">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-1">Alur Pendaftaran</span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-list-check text-sky-600"></i>
                            {{ __('ui.partner_steps_title') }}
                        </h2>
                    </div>
                    
                    <div class="space-y-2.5">
                        @foreach (__('ui.partner_steps') as $i => $step)
                            <div class="flex items-center gap-3.5 rounded-xl bg-white border border-slate-200/80 px-4 py-3 shadow-sm hover:border-sky-300 transition duration-200">
                                <span class="w-7 h-7 shrink-0 rounded-lg bg-sky-500 text-white font-black text-xs flex items-center justify-center shadow-sm">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs text-slate-900 leading-tight">{{ $step['title'] }}</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- FAQ Singkat --}}
                <div>
                    <div class="mb-5">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block mb-1">Pertanyaan Umum</span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-question text-sky-600"></i>
                            {{ __('ui.partner_faq_title') }}
                        </h2>
                    </div>

                    <div class="space-y-2.5">
                        @foreach (__('ui.partner_faq') as $faq)
                            <details class="rounded-xl bg-white border border-slate-200/80 px-4 py-3 group shadow-sm">
                                <summary class="font-bold text-xs cursor-pointer list-none flex justify-between items-center text-slate-900">
                                    <span>{{ $faq['q'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] transition group-open:rotate-180 ml-2 text-sky-600"></i>
                                </summary>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed border-t border-slate-100 pt-2.5">
                                    {{ $faq['a'] }}
                                </p>
                            </details>
                        @endforeach
                    </div>
                </div>

            </div>
        </section>

        {{-- 5. FORMULIR PENDAFTARAN MITRA --}}
        <section id="register-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">
            <div class="bg-white rounded-3xl p-6 sm:p-10 lg:p-12 shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                    
                    {{-- Sisi Kiri: Visual Info --}}
                    <div class="lg:col-span-5 space-y-6">
                        <span class="text-xs font-black text-sky-600 uppercase tracking-widest block">Langkah Terakhir</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-snug">
                            Siap Mengembangkan Usaha Bersama SekalaGo?
                        </h2>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Isi formulir di samping untuk mendaftarkan properti atau armada kendaraan Anda. Tim operasional kami akan memverifikasi data Anda dalam waktu 1x24 jam.
                        </p>

                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 mt-0.5 border border-sky-100">
                                    <i class="fa-solid fa-bolt text-xs"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-900">Proses Cepat & Langsung Aktif</h4>
                                    <p class="text-[11px] text-slate-500">Akun mitra langsung siap dikelola setelah verifikasi selesai.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 mt-0.5 border border-sky-100">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-900">Kerahasiaan Data Terjamin</h4>
                                    <p class="text-[11px] text-slate-500">Seluruh informasi legalitas dan identitas disimpan secara aman.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Box Bantuan --}}
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-info text-sky-600 text-lg"></i>
                                <span class="text-xs font-medium text-slate-600">Butuh bantuan pendaftaran?</span>
                            </div>
                            <a href="https://wa.me/" target="_blank" class="text-xs font-bold text-sky-600 hover:underline">Hubungi Kami →</a>
                        </div>
                    </div>

                    {{-- Sisi Kanan: Form Pendaftaran --}}
                    <div class="lg:col-span-7 bg-slate-50/80 rounded-2xl p-6 sm:p-8 border border-slate-200/80">
                        <div class="mb-6">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('ui.register_partner') }}</h3>
                            <p class="text-slate-500 text-xs mt-0.5">{{ __('ui.register_partner_hint') }}</p>
                        </div>

                        <form method="POST" action="{{ route('register.partner.store') }}" class="space-y-4">
                            @csrf

                            @if ($errors->any())
                                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @php
                                $box = 'bg-white p-3.5 rounded-xl border border-slate-200/90 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/10 transition shadow-sm';
                                $label = 'block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1';
                                $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none placeholder-slate-400';
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-building text-sky-600 mr-1"></i> {{ __('ui.company_name') }}</label>
                                    <input name="company_name" value="{{ old('company_name') }}" placeholder="{{ __('ui.company_name') }}" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-id-card text-sky-600 mr-1"></i> {{ __('ui.business_license_no') }}</label>
                                    <input name="business_license_no" value="{{ old('business_license_no') }}" placeholder="{{ __('ui.business_license_no') }}" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-building-columns text-sky-600 mr-1"></i> {{ __('ui.bank_account_no') }}</label>
                                    <input name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="{{ __('ui.bank_account_no') }}" class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-phone text-sky-600 mr-1"></i> {{ __('ui.phone') }}</label>
                                    <input name="phone_number" value="{{ old('phone_number') }}" placeholder="{{ __('ui.phone') }}" class="{{ $input }}">
                                </div>

                                <div class="{{ $box }} sm:col-span-2">
                                    <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sky-600 mr-1"></i> {{ __('ui.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.password') }}</label>
                                    <input type="password" name="password" placeholder="{{ __('ui.password') }}" required class="{{ $input }}">
                                </div>

                                <div class="{{ $box }}">
                                    <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.password_confirm') }}</label>
                                    <input type="password" name="password_confirmation" placeholder="{{ __('ui.password_confirm') }}" required class="{{ $input }}">
                                </div>
                            </div>

                            <button class="w-full py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow transition duration-300 flex items-center justify-center gap-2 mt-4 text-sm">
                                <i class="fa-solid fa-handshake"></i> {{ __('ui.register') }}
                            </button>
                        </form>

                        <p class="text-center text-xs text-slate-500 mt-5">
                            Sudah punya akun mitra? <a href="{{ route('login') }}" class="font-bold text-sky-600 hover:underline">{{ __('ui.login') }}</a>
                        </p>
                    </div>

                </div>
            </div>
        </section>

    </div>
@endsection