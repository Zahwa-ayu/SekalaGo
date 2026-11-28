@extends('layouts.storefront')

@section('content')
    @php
        // Gaya kolom isian: sama dengan formulir pendaftaran korporat
        $box     = '';
        $label   = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input   = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $file    = 'block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100';
        $drop    = '';
        $badge   = 'w-8 h-8 shrink-0 rounded-xl bg-sky-500 text-white font-black text-xs flex items-center justify-center shadow-md shadow-sky-500/20';
        $btnNext = 'px-6 py-3 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 disabled:opacity-60 disabled:cursor-wait text-white text-sm font-bold rounded-xl shadow-lg shadow-sky-500/20 transition inline-flex items-center justify-center gap-2';
        $btnBack = 'px-5 py-3 border border-slate-300 text-slate-600 hover:bg-slate-100 text-sm font-bold rounded-xl transition inline-flex items-center justify-center gap-2';

        // Langkah formulir (satu langkah = satu slide)
        $formSteps = [
            1 => __('ui.partner_business_data'),
            2 => __('ui.corporate_documents'),
            3 => __('ui.partner_contact_account'),
            4 => __('ui.payout_bank_account'),
        ];

        // Kolom di tiap langkah: dipakai untuk membuka formulir di langkah yang bermasalah
        // dan oleh skrip validasi di bawah.
        $stepFields = [
            1 => ['company_name', 'legal_form', 'business_type', 'tax_id', 'business_license_no', 'address', 'city_id', 'phone', 'website'],
            2 => ['tax_document', 'license_document', 'supporting_document'],
            3 => ['contact_name', 'phone_number', 'email', 'password', 'password_confirmation'],
            4 => ['bank_name', 'bank_account_no', 'bank_account_name', 'proposed_commission', 'commission_proposal_note', 'terms'],
        ];
        $startStep = 1;
        if ($errors->any()) {
            foreach ($stepFields as $number => $fields) {
                if (collect($fields)->contains(fn ($field) => $errors->has($field))) {
                    $startStep = $number;
                    break;
                }
            }
        }
    @endphp

    <div class="bg-slate-50 text-slate-800 min-h-screen font-sans antialiased">

        {{-- 1. HERO BANNER --}}
        <section class="relative min-h-screen flex items-center pt-28 sm:pt-32 pb-20 bg-neutral-800 text-white overflow-hidden">

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
        <section id="about-partnership" class="bg-white py-16 border-b border-slate-200/80 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

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

                    <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                        @foreach ([
                            ['fa-building', 'B2B & B2C', 'Jangkauan pasar wisatawan & pelanggan korporasi.'],
                            ['fa-chart-line', 'Real-Time', 'Laporan pemesanan & manajemen unit instan.'],
                            ['fa-shield-halved', 'Transparan', 'Pencairan dana jelas & pembayaran tergaransi.'],
                            ['fa-headset', 'Dukungan', 'Tim operasional siap membantu kemitraan Anda.'],
                        ] as [$icon, $title, $desc])
                            <div class="bg-slate-50 border border-slate-200 hover:border-sky-300 p-5 rounded-2xl shadow-sm hover:shadow-md transition duration-300 group">
                                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 text-base border border-sky-100 transition duration-300">
                                    <i class="fa-solid {{ $icon }}"></i>
                                </div>
                                <h3 class="text-base font-black text-slate-900">{{ $title }}</h3>
                                <p class="text-xs text-slate-500 mt-1">{{ $desc }}</p>
                            </div>
                        @endforeach
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
                                <span class="{{ $badge }}">{{ $i + 1 }}</span>
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

        {{-- 5. FORMULIR PENDAFTARAN MITRA (per langkah / per slide) --}}
        <section id="register-section" class="relative overflow-hidden bg-neutral-800 py-16 sm:py-24 scroll-mt-16">

            {{-- Background sama seperti hero --}}
            <div class="absolute inset-0 z-0 select-none">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1920&q=80"
                     alt="" class="w-full h-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/55 to-black/70"></div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-10">

                {{-- Sambutan di atas kartu --}}
                <div class="text-center text-white space-y-4">
                    <span class="text-xs font-black text-sky-300 uppercase tracking-widest block">Langkah Terakhir</span>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-[1.15] drop-shadow-md">
                        Siap Mengembangkan Usaha Bersama SekalaGo?
                    </h2>
                    <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto drop-shadow-md">
                        Isi formulir pendaftaran untuk mendaftarkan properti akomodasi atau armada kendaraan Anda. Tim operasional kami akan memverifikasi data Anda dalam waktu 1x24 jam.
                    </p>
                    <div class="flex flex-wrap justify-center gap-3 pt-2">
                        <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/10 border border-white/25 backdrop-blur-md text-xs font-semibold text-white/90">
                            <i class="fa-solid fa-bolt text-sky-300"></i> Proses Cepat & Langsung Aktif
                        </span>
                        <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/10 border border-white/25 backdrop-blur-md text-xs font-semibold text-white/90">
                            <i class="fa-solid fa-lock text-sky-300"></i> Kerahasiaan Data Terjamin
                        </span>
                    </div>
                </div>

                {{-- Kartu berisi wizard --}}
                <div id="wizard-card" class="bg-white rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/30 border border-white/20 scroll-mt-20">

                        <div class="mb-6">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ __('ui.register_partner') }}</h3>
                            <p class="text-slate-500 text-xs mt-1">{{ __('ui.register_partner_hint') }}</p>
                        </div>

                        {{-- Penunjuk langkah (langkah yang sudah dilewati bisa diklik untuk kembali) --}}
                        <div class="flex items-center gap-2 sm:gap-3 text-xs font-bold">
                            @foreach ($formSteps as $num => $title)
                                <button type="button" data-step-dot="{{ $num }}" class="flex items-center gap-2 transition">
                                    <span data-dot-circle class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] shrink-0 transition">
                                        <span data-dot-num>{{ $num }}</span>
                                        <i data-dot-check class="fa-solid fa-check hidden"></i>
                                    </span>
                                    <span data-dot-label class="hidden sm:inline transition">{{ $title }}</span>
                                </button>
                                @unless ($loop->last)
                                    <span data-step-line="{{ $num }}" class="flex-1 h-0.5 rounded bg-slate-200 transition-colors"></span>
                                @endunless
                            @endforeach
                        </div>
                        <p id="step-counter" class="text-[11px] text-slate-400 mt-3 mb-8"></p>

                        {{-- Pesan umum (mis. gagal menghubungi server) --}}
                        <p id="wizard-message" class="hidden mb-6 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs" role="alert"></p>

                        {{-- novalidate: pengecekan dikerjakan skrip di bawah, per langkah, dengan pesan di bawah tiap kolom --}}
                        <form id="partner-form" method="POST" action="{{ route('register.partner.store') }}" enctype="multipart/form-data"
                              novalidate data-start="{{ $startStep }}"
                              data-validate-url="{{ Route::has('register.partner.validate') ? route('register.partner.validate') : '' }}">
                            @csrf

                            {{-- LANGKAH 1: DATA USAHA --}}
                            <div data-step-panel="1" class="wizard-panel space-y-5">
                                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                                    <span class="{{ $badge }}">1</span>
                                    <h4 class="font-extrabold text-slate-900">{{ __('ui.partner_business_data') }}</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
                                    <div data-box class="{{ $box }} sm:col-span-4">
                                        <label class="{{ $label }}"><i class="fa-solid fa-building text-sky-600 mr-1"></i> {{ __('ui.company_name_legal') }}</label>
                                        <input name="company_name" required maxlength="255" value="{{ old('company_name') }}" placeholder="PT / CV / Nama Usaha" class="{{ $input }}">
                                    </div>
                                    <div data-box class="{{ $box }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-scale-balanced text-sky-600 mr-1"></i> {{ __('ui.legal_form') }}</label>
                                        <select name="legal_form" required class="{{ $input }} cursor-pointer">
                                            <option value="">-</option>
                                            @foreach ($legalForms as $form)
                                                <option value="{{ $form }}" @selected(old('legal_form') === $form)>{{ $form }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <span class="{{ $label }}">{{ __('ui.business_type') }}</span>
                                        <div data-box class="grid sm:grid-cols-3 gap-2 rounded-xl">
                                            @foreach ($businessTypes as $type)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="business_type" value="{{ $type }}" required class="peer sr-only" @checked(old('business_type') === $type)>
                                                    <span class="block text-center px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600
                                                                 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-600 hover:bg-slate-50 transition">
                                                        {{ __('ui.business_type_' . $type) }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div data-box class="{{ $box }} sm:col-span-3">
                                        <label class="{{ $label }}"><i class="fa-solid fa-file-invoice text-sky-600 mr-1"></i> {{ __('ui.tax_id') }}</label>
                                        <input name="tax_id" required inputmode="numeric" maxlength="25" value="{{ old('tax_id') }}" placeholder="00.000.000.0-000.000" class="{{ $input }} font-mono">
                                    </div>
                                    <div data-box class="{{ $box }} sm:col-span-3">
                                        <label class="{{ $label }}"><i class="fa-solid fa-id-card text-sky-600 mr-1"></i> {{ __('ui.business_license_no') }}</label>
                                        <input name="business_license_no" required maxlength="30" value="{{ old('business_license_no') }}" placeholder="NIB / Nomor Izin Usaha" class="{{ $input }} font-mono">
                                    </div>

                                    <div data-box class="{{ $box }} sm:col-span-6">
                                        <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sky-600 mr-1"></i> {{ __('ui.company_address') }}</label>
                                        <input name="address" required maxlength="255" value="{{ old('address') }}" class="{{ $input }}">
                                    </div>

                                    <div data-box class="{{ $box }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-city text-sky-600 mr-1"></i> {{ __('ui.city_name') }}</label>
                                        <select name="city_id" required class="{{ $input }} cursor-pointer">
                                            <option value="">{{ __('ui.choose_city') }}</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}" @selected((int) old('city_id') === $city->id)>{{ $city->name }}@if ($city->province), {{ $city->province }}@endif</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div data-box class="{{ $box }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-phone text-sky-600 mr-1"></i> {{ __('ui.company_phone') }}</label>
                                        <input name="phone" required inputmode="tel" maxlength="20" value="{{ old('phone') }}" class="{{ $input }}">
                                    </div>
                                    <div data-box class="{{ $box }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-globe text-sky-600 mr-1"></i> {{ __('ui.website') }}</label>
                                        <input type="url" name="website" maxlength="255" value="{{ old('website') }}" placeholder="https://" class="{{ $input }}">
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <button type="button" data-next class="{{ $btnNext }}">Lanjut <i class="fa-solid fa-arrow-right text-xs"></i></button>
                                </div>
                            </div>

                            {{-- LANGKAH 2: DOKUMEN --}}
                            <div data-step-panel="2" class="wizard-panel space-y-5 hidden">
                                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                                    <span class="{{ $badge }}">2</span>
                                    <h4 class="font-extrabold text-slate-900">{{ __('ui.corporate_documents') }}</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div data-box class="{{ $drop }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-file-pdf text-sky-600 mr-1"></i> {{ __('ui.tax_document') }} *</label>
                                        <input type="file" name="tax_document" required accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                                    </div>
                                    <div data-box class="{{ $drop }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-file-pdf text-sky-600 mr-1"></i> {{ __('ui.license_document') }} *</label>
                                        <input type="file" name="license_document" required accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                                    </div>
                                    <div data-box class="{{ $drop }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-paperclip text-sky-600 mr-1"></i> {{ __('ui.supporting_document') }} <span class="normal-case font-semibold">({{ __('ui.optional') }})</span></label>
                                        <input type="file" name="supporting_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.supporting_document_hint') }}</p>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400"><i class="fa-solid fa-circle-info"></i> {{ __('ui.corporate_documents_hint') }}</p>

                                @if ($errors->any())
                                    <p class="text-[11px] text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> Setelah halaman dimuat ulang, file perlu dipilih kembali.</p>
                                @endif

                                <div class="flex items-center justify-between gap-3 pt-4">
                                    <button type="button" data-prev class="{{ $btnBack }}"><i class="fa-solid fa-arrow-left text-xs"></i> Kembali</button>
                                    <button type="button" data-next class="{{ $btnNext }}">Lanjut <i class="fa-solid fa-arrow-right text-xs"></i></button>
                                </div>
                            </div>

                            {{-- LANGKAH 3: PENANGGUNG JAWAB & AKUN --}}
                            <div data-step-panel="3" class="wizard-panel space-y-5 hidden">
                                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                                    <span class="{{ $badge }}">3</span>
                                    <h4 class="font-extrabold text-slate-900">{{ __('ui.partner_contact_account') }}</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div data-box class="{{ $box }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-user text-sky-600 mr-1"></i> {{ __('ui.contact_person') }}</label>
                                        <input name="contact_name" required maxlength="255" value="{{ old('contact_name') }}" class="{{ $input }}">
                                    </div>
                                    <div data-box class="{{ $box }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-phone text-sky-600 mr-1"></i> {{ __('ui.contact_phone') }}</label>
                                        <input name="phone_number" required inputmode="tel" maxlength="20" value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                                    </div>
                                    <div data-box class="{{ $box }} sm:col-span-2">
                                        <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sky-600 mr-1"></i> {{ __('ui.email') }}</label>
                                        <input type="email" name="email" required maxlength="255" value="{{ old('email') }}" placeholder="email@perusahaan.com" class="{{ $input }}">
                                    </div>
                                    <div>
                                        <div data-box class="{{ $box }}">
                                            <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.password') }}</label>
                                            <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="{{ $input }}">
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-1.5">{{ __('ui.password_rules_hint') }}</p>
                                    </div>
                                    <div data-box class="{{ $box }} self-start">
                                        <label class="{{ $label }}"><i class="fa-solid fa-lock text-sky-600 mr-1"></i> {{ __('ui.confirm_password') }}</label>
                                        <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="{{ $input }}">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3 pt-4">
                                    <button type="button" data-prev class="{{ $btnBack }}"><i class="fa-solid fa-arrow-left text-xs"></i> Kembali</button>
                                    <button type="button" data-next class="{{ $btnNext }}">Lanjut <i class="fa-solid fa-arrow-right text-xs"></i></button>
                                </div>
                            </div>

                            {{-- LANGKAH 4: REKENING & USULAN KOMISI (OPSIONAL) + KIRIM --}}
                            <div data-step-panel="4" class="wizard-panel space-y-5 hidden">
                                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                                    <span class="{{ $badge }}">4</span>
                                    <div>
                                        <h4 class="font-extrabold text-slate-900">{{ __('ui.payout_bank_account') }} <span class="text-xs font-semibold text-slate-400">({{ __('ui.optional') }})</span></h4>
                                        <p class="text-xs text-slate-500">{{ __('ui.bank_account_hint') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div data-box class="{{ $box }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-building-columns text-sky-600 mr-1"></i> {{ __('ui.bank_name') }}</label>
                                        <input name="bank_name" list="bank-list" maxlength="60" value="{{ old('bank_name') }}" class="{{ $input }}">
                                        <datalist id="bank-list">
                                            @foreach (config('sekalago.banks') as $bank)<option value="{{ $bank }}"></option>@endforeach
                                        </datalist>
                                    </div>
                                    <div data-box class="{{ $box }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-hashtag text-sky-600 mr-1"></i> {{ __('ui.bank_account_no') }}</label>
                                        <input name="bank_account_no" inputmode="numeric" maxlength="50" value="{{ old('bank_account_no') }}" class="{{ $input }} font-mono">
                                    </div>
                                    <div data-box class="{{ $box }}">
                                        <label class="{{ $label }}"><i class="fa-solid fa-user-tag text-sky-600 mr-1"></i> {{ __('ui.bank_account_name') }}</label>
                                        <input name="bank_account_name" maxlength="100" value="{{ old('bank_account_name') }}" class="{{ $input }}">
                                    </div>
                                </div>

                                {{-- Usulan komisi --}}
                                <div class="rounded-2xl bg-sky-50 border border-sky-100 p-4 space-y-3">
                                    <div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ __('ui.commission_proposal') }} <span class="text-xs font-semibold text-slate-400">({{ __('ui.optional') }})</span></div>
                                        <p class="text-xs text-slate-500">{{ __('ui.commission_proposal_hint') }}</p>
                                    </div>

                                    <div class="grid sm:grid-cols-3 gap-4">
                                        <div data-box>
                                            <label class="{{ $label }}">{{ __('ui.proposed_commission') }}</label>
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="proposed_commission" min="10" max="25" step="0.5" value="{{ old('proposed_commission') }}" placeholder="15" class="{{ $input }}">
                                                <span class="text-sm font-bold text-slate-500">%</span>
                                            </div>
                                        </div>
                                        <div data-box class="sm:col-span-2">
                                            <label class="{{ $label }}">{{ __('ui.commission_proposal_note') }}</label>
                                            <input name="commission_proposal_note" maxlength="300" value="{{ old('commission_proposal_note') }}" class="{{ $input }}">
                                        </div>
                                    </div>
                                </div>

                                <label class="block text-sm text-slate-600 pt-2">
                                    <span class="flex items-start gap-3">
                                        <input type="checkbox" name="terms" value="1" required @checked(old('terms')) class="mt-0.5 rounded border-slate-300 text-sky-500">
                                        <span>
                                            <span>{{ __('ui.partner_terms') }}</span>
                                            <a href="{{ route('pages.show', 'terms') }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline whitespace-nowrap">{{ __('ui.read_terms') }}</a>
                                            <span class="text-slate-300">&middot;</span>
                                            <a href="{{ route('pages.show', 'privacy') }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline whitespace-nowrap">{{ __('ui.read_privacy') }}</a>
                                        </span>
                                    </span>
                                </label>

                                <div class="rounded-2xl bg-slate-50 border border-slate-200 px-5 py-4 text-xs text-slate-700 space-y-1">
                                    <div class="font-extrabold"><i class="fa-solid fa-circle-info text-sky-500"></i> {{ __('ui.corporate_next_title') }}</div>
                                    <p>{{ __('ui.partner_next_steps') }}</p>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4">
                                    <button type="button" data-prev class="{{ $btnBack }}"><i class="fa-solid fa-arrow-left text-xs"></i> Kembali</button>
                                    <button type="submit" class="{{ $btnNext }} sm:flex-1 py-3.5 font-black">
                                        <i class="fa-solid fa-handshake"></i> {{ __('ui.partner_register_submit') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <p class="text-center text-xs text-slate-500 mt-8">
                            {{ __('ui.already_have_account') }}
                            <a href="{{ route('login') }}" class="font-extrabold text-sky-600 hover:text-sky-700 hover:underline">{{ __('ui.login') }}</a>
                        </p>
                    </div>

                {{-- Bantuan --}}
                @include('partials.register-help')

            </div>
        </section>

    </div>
@endsection

@push('head')
    <style>
        /* Perpindahan antar langkah: geser tipis dari kanan */
        .wizard-panel { animation: wizard-in .25s ease; }
        @keyframes wizard-in {
            from { opacity: 0; transform: translateX(16px); }
            to   { opacity: 1; transform: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .wizard-panel { animation: none; }
        }

        /* Kolom yang salah: garis merah, pesan muncul di bawah isian */
        .wizard-error input:not([type="radio"]):not([type="checkbox"]),
        .wizard-error select { border-color: #f87171 !important; background-color: #fef2f2 !important; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('partner-form');
            if (!form) return;

            const card      = document.getElementById('wizard-card');
            const panels    = Array.from(form.querySelectorAll('[data-step-panel]'));
            const dots      = Array.from(document.querySelectorAll('[data-step-dot]'));
            const lines     = Array.from(document.querySelectorAll('[data-step-line]'));
            const counter   = document.getElementById('step-counter');
            const messageEl = document.getElementById('wizard-message');
            const total     = panels.length;
            const stepFields   = @json($stepFields);
            const serverErrors = @json($errors->getMessages());
            let current     = Math.min(Math.max(parseInt(form.dataset.start, 10) || 1, 1), total);
            let okSignature = null; // isian langkah 1 yang sudah lolos pengecekan server

            // Radio group dikembalikan sebagai RadioNodeList: ambil elemen pertamanya
            const el = (name) => {
                const item = form.elements[name];
                return item instanceof RadioNodeList ? item[0] : item;
            };
            const fieldsOf = (step) => stepFields[step].map(el).filter(Boolean);

            /* ---------- Aturan validasi (cermin dari PartnerRegistrationController) ---------- */

            const text  = (field) => (field.value || '').trim();
            const empty = (field, message) => (text(field) === '' ? message : '');

            const phoneRule = (field) => {
                if (text(field) === '') return 'Nomor telepon wajib diisi.';
                return /^\+?[0-9\s\-]{8,20}$/.test(text(field)) ? '' : 'Nomor telepon tidak valid (8–20 digit).';
            };

            const fileRule = (field, required) => {
                const file = field.files && field.files[0];
                if (!file) return required ? 'Dokumen wajib diunggah.' : '';
                if (!/\.(pdf|jpe?g|png|webp)$/i.test(file.name)) return 'Format file harus PDF, JPG, PNG, atau WEBP.';
                if (file.size > 4096 * 1024) return 'Ukuran file maksimal 4 MB.';
                return '';
            };

            // Rekening: opsional, tetapi kalau salah satu diisi, ketiganya harus lengkap
            const bankFilled = () => ['bank_name', 'bank_account_no', 'bank_account_name'].some((name) => text(el(name)) !== '');
            const bankRequired = (field, message) => (bankFilled() && text(field) === '' ? message : '');

            const rules = {
                company_name: (f) => empty(f, 'Nama perusahaan wajib diisi.'),
                legal_form:   (f) => empty(f, 'Pilih bentuk badan usaha.'),
                business_type: () => (form.querySelector('[name="business_type"]:checked') ? '' : 'Pilih jenis usaha.'),
                tax_id: (f) => {
                    const digits = text(f).replace(/\D/g, '');
                    if (digits === '') return 'NPWP wajib diisi.';
                    return digits.length >= 15 && digits.length <= 16 ? '' : 'NPWP harus 15–16 digit angka.';
                },
                business_license_no: (f) => {
                    if (text(f) === '') return 'Nomor izin usaha wajib diisi.';
                    return /^[0-9A-Za-z.\-\/]{8,30}$/.test(text(f)) ? '' : 'Nomor izin usaha 8–30 karakter (huruf, angka, titik, strip, atau garis miring).';
                },
                address: (f) => empty(f, 'Alamat perusahaan wajib diisi.'),
                city_id: (f) => empty(f, 'Pilih kota.'),
                phone:   phoneRule,
                website: (f) => {
                    if (text(f) === '') return '';
                    return /^https?:\/\/\S+\.\S+/i.test(text(f)) ? '' : 'Alamat website harus diawali http:// atau https://.';
                },

                tax_document:        (f) => fileRule(f, true),
                license_document:    (f) => fileRule(f, true),
                supporting_document: (f) => fileRule(f, false),

                contact_name: (f) => empty(f, 'Nama penanggung jawab wajib diisi.'),
                phone_number: phoneRule,
                email: (f) => {
                    if (text(f) === '') return 'Email wajib diisi.';
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text(f)) ? '' : 'Format email tidak valid.';
                },
                password: (f) => {
                    const value = f.value || '';
                    if (value === '') return 'Kata sandi wajib diisi.';
                    return value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value)
                        ? '' : 'Kata sandi minimal 8 karakter dan memuat huruf serta angka.';
                },
                password_confirmation: (f) => {
                    if ((f.value || '') === '') return 'Konfirmasi kata sandi wajib diisi.';
                    return f.value === el('password').value ? '' : 'Konfirmasi kata sandi tidak sama.';
                },

                bank_name:         (f) => bankRequired(f, 'Nama bank wajib diisi jika rekening diisi.'),
                bank_account_no:   (f) => {
                    const missing = bankRequired(f, 'Nomor rekening wajib diisi jika rekening diisi.');
                    if (missing) return missing;
                    return text(f) === '' || /^[0-9\s\-]+$/.test(text(f)) ? '' : 'Nomor rekening hanya boleh berisi angka.';
                },
                bank_account_name: (f) => bankRequired(f, 'Nama pemilik rekening wajib diisi jika rekening diisi.'),

                proposed_commission: (f) => {
                    if (text(f) === '') return '';
                    const value = parseFloat(text(f));
                    return value >= 10 && value <= 25 ? '' : 'Usulan komisi harus antara 10% dan 25%.';
                },
                terms: (f) => (f.checked ? '' : 'Anda harus menyetujui syarat dan ketentuan.'),
            };

            /* ---------- Pesan error di bawah kolom ---------- */

            const boxOf = (field) => field.closest('[data-box]') || field.closest('label');

            function setError(field, message) {
                const box = boxOf(field);
                if (!box) return;

                let note = box.querySelector('[data-error]');
                if (!note) {
                    note = document.createElement('p');
                    note.setAttribute('data-error', '');
                    note.className = 'text-[11px] font-semibold text-red-600 mt-1.5';
                    box.appendChild(note);
                }

                note.textContent = message;
                box.classList.add('wizard-error');
            }

            function clearError(field) {
                const box = boxOf(field);
                if (!box) return;

                box.querySelector('[data-error]')?.remove();
                box.classList.remove('wizard-error');
            }

            const showMessage = (message) => { messageEl.textContent = message; messageEl.classList.remove('hidden'); };
            const hideMessage = () => messageEl.classList.add('hidden');

            // Pesan hilang begitu kolomnya diperbaiki
            form.addEventListener('input',  (event) => clearError(event.target));
            form.addEventListener('change', (event) => clearError(event.target));

            /* ---------- Validasi per langkah ---------- */

            function focusFirstError(step) {
                const field = fieldsOf(step).find((item) => boxOf(item)?.classList.contains('wizard-error'));
                field?.focus();
            }

            // Cek di browser: tampilkan semua kolom yang salah di langkah ini sekaligus
            function validateClient(step) {
                let valid = true;

                fieldsOf(step).forEach((field) => {
                    const check   = rules[field.name];
                    const message = check ? check(field) : '';

                    if (message) {
                        setError(field, message);
                        valid = false;
                    } else {
                        clearError(field);
                    }
                });

                if (!valid) focusFirstError(step);

                return valid;
            }

            // Langkah 1 juga dicek ke server (bila rutenya ada): nama usaha dan NPWP yang sudah terdaftar baru ketahuan di sana
            async function validateServer(button) {
                const url = form.dataset.validateUrl;
                if (!url) return true;

                const body = new FormData();
                stepFields[1].forEach((name) => {
                    const item = form.elements[name];
                    const value = item instanceof RadioNodeList ? item.value : item.value;
                    body.append(name, value);
                });

                const signature = JSON.stringify(Array.from(body.entries()));
                if (signature === okSignature) return true;

                const original = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Memeriksa...';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        },
                        body,
                    });

                    if (response.ok) {
                        okSignature = signature;
                        return true;
                    }

                    if (response.status === 422) {
                        const payload = await response.json();

                        Object.entries(payload.errors || {}).forEach(([name, messages]) => {
                            if (el(name)) setError(el(name), messages[0]);
                        });
                        focusFirstError(1);
                        return false;
                    }

                    showMessage(response.status === 429
                        ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                        : 'Data belum bisa diperiksa. Silakan coba lagi.');
                    return false;
                } catch (error) {
                    showMessage('Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
                    return false;
                } finally {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }

            /* ---------- Tampilan slide dan penunjuk langkah ---------- */

            const dotStyles = {
                active: ['bg-sky-500', 'text-white', 'ring-4', 'ring-sky-100'],
                done:   ['bg-sky-500', 'text-white'],
                todo:   ['bg-slate-200', 'text-slate-500'],
            };
            const labelStyles = {
                active: ['text-sky-600'],
                done:   ['text-slate-700'],
                todo:   ['text-slate-400'],
            };

            function render() {
                panels.forEach((panel, index) => panel.classList.toggle('hidden', index + 1 !== current));

                dots.forEach((dot, index) => {
                    const step   = index + 1;
                    const state  = step === current ? 'active' : (step < current ? 'done' : 'todo');
                    const circle = dot.querySelector('[data-dot-circle]');
                    const label  = dot.querySelector('[data-dot-label]');

                    Object.values(dotStyles).flat().forEach((c) => circle.classList.remove(c));
                    Object.values(labelStyles).flat().forEach((c) => label.classList.remove(c));
                    circle.classList.add(...dotStyles[state]);
                    label.classList.add(...labelStyles[state]);

                    dot.querySelector('[data-dot-num]').classList.toggle('hidden', state === 'done');
                    dot.querySelector('[data-dot-check]').classList.toggle('hidden', state !== 'done');
                    dot.classList.toggle('cursor-default', step >= current);
                });

                lines.forEach((line, index) => {
                    const done = index + 1 < current;
                    line.classList.toggle('bg-sky-500', done);
                    line.classList.toggle('bg-slate-200', !done);
                });

                counter.textContent = 'Langkah ' + current + ' dari ' + total;
            }

            function go(step, scroll) {
                current = Math.min(Math.max(step, 1), total);
                hideMessage();
                render();

                if (scroll) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            /* ---------- Tombol ---------- */

            // "Lanjut": cek di browser dulu, lalu (khusus langkah 1) cek ke server
            form.querySelectorAll('[data-next]').forEach((button) => {
                button.addEventListener('click', async () => {
                    hideMessage();

                    if (!validateClient(current)) return;
                    if (current === 1 && !(await validateServer(button))) return;

                    go(current + 1, true);
                });
            });

            form.querySelectorAll('[data-prev]').forEach((button) => {
                button.addEventListener('click', () => go(current - 1, true));
            });

            // Langkah yang sudah dilewati boleh diklik untuk kembali (maju hanya lewat "Lanjut")
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    if (index + 1 < current) go(index + 1, true);
                });
            });

            // Enter di tengah pengisian = "Lanjut", bukan kirim formulir
            form.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || current >= total || event.target.tagName === 'TEXTAREA') return;

                event.preventDefault();
                panels[current - 1].querySelector('[data-next]')?.click();
            });

            // Kirim: periksa ulang semua langkah di browser (langkah 1 sudah dicek server saat "Lanjut")
            form.addEventListener('submit', (event) => {
                for (let step = 1; step <= total; step++) {
                    if (!validateClient(step)) {
                        event.preventDefault();
                        go(step, false);
                        focusFirstError(step);
                        return;
                    }
                }
            });

            /* ---------- Awal ---------- */

            // Error dari server (setelah kirim) ditampilkan juga di bawah kolomnya
            Object.entries(serverErrors).forEach(([name, messages]) => {
                if (el(name)) setError(el(name), messages[0]);
            });

            render();
        })();
    </script>
@endpush