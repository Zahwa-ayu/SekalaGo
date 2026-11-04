@extends('layouts.app')

@section('content')
    <div class="hero-bg min-h-screen text-white">
        
        {{-- 1. HERO BANNER (Full Width) --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg mb-6">
                <i class="fa-solid fa-handshake text-sagara-dark text-3xl"></i>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight max-w-4xl mx-auto leading-tight">
                {{ __('ui.partner_why_title') }}
            </h1>
            <p class="text-sky-200 text-base sm:text-lg mt-4 max-w-2xl mx-auto font-normal leading-relaxed">
                {{ __('ui.partner_why_hint') }}
            </p>
        </section>

        {{-- 2. SEKSI KEUNTUNGAN MITRA (Grid 3 Kolom Full-Width) --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @php
                $icons = ['fa-store', 'fa-briefcase', 'fa-bed', 'fa-receipt', 'fa-language', 'fa-file-signature'];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (__('ui.partner_benefits') as $i => $benefit)
                    <div class="rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md p-6 hover:bg-white/15 transition duration-300">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center mb-4 shadow">
                            <i class="fa-solid {{ $icons[$i] ?? 'fa-star' }} text-sagara-dark text-lg"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-white">{{ $benefit['title'] }}</h3>
                        <p class="text-sm text-sky-100 leading-relaxed">{{ $benefit['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 3. SEKSI LANGKAH BERGABUNG & FAQ (Layout 2 Kolom Berdampingan) --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                
                {{-- Langkah Bergabung --}}
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-list-check text-sagara-teal"></i>
                        {{ __('ui.partner_steps_title') }}
                    </h2>
                    <ol class="space-y-4">
                        @foreach (__('ui.partner_steps') as $i => $step)
                            <li class="flex items-start gap-4 rounded-2xl bg-white/10 border border-white/20 p-5 backdrop-blur-md">
                                <span class="w-9 h-9 shrink-0 rounded-full bg-sagara-teal text-sagara-dark font-black text-sm flex items-center justify-center mt-0.5 shadow">
                                    {{ $i + 1 }}
                                </span>
                                <div>
                                    <p class="font-bold text-base text-white">{{ $step['title'] }}</p>
                                    <p class="text-sm text-sky-100 mt-1 leading-relaxed">{{ $step['desc'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                {{-- FAQ Singkat --}}
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-circle-question text-sagara-teal"></i>
                        {{ __('ui.partner_faq_title') }}
                    </h2>
                    <div class="space-y-3">
                        @foreach (__('ui.partner_faq') as $faq)
                            <details class="rounded-2xl bg-white/10 border border-white/20 p-5 group backdrop-blur-md">
                                <summary class="font-bold text-base cursor-pointer list-none flex justify-between items-center text-white">
                                    <span>{{ $faq['q'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs transition group-open:rotate-180 ml-2 text-sagara-teal"></i>
                                </summary>
                                <p class="text-sm text-sky-100 mt-3 leading-relaxed border-t border-white/10 pt-3">
                                    {{ $faq['a'] }}
                                </p>
                            </details>
                        @endforeach
                    </div>
                </div>

            </div>
        </section>

        {{-- 4. FORMULIR PENDAFTARAN (Seksi Penutup dengan Container Terkontrol) --}}
        <section id="register-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20">
            <div class="max-w-2xl mx-auto">
                <div class="glass-card rounded-3xl p-6 sm:p-10 shadow-2xl border border-white/60">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ __('ui.register_partner') }}</h2>
                        <p class="text-slate-500 text-sm mt-1">{{ __('ui.register_partner_hint') }}</p>
                    </div>

                    <form method="POST" action="{{ route('register.partner.store') }}" class="space-y-4">
                        @csrf

                        @if ($errors->any())
                            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @php
                            $box = 'bg-slate-50 p-3.5 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
                            $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
                            $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-building text-sagara-blue"></i> {{ __('ui.company_name') }}</label>
                                <input name="company_name" value="{{ old('company_name') }}" placeholder="{{ __('ui.company_name') }}" required class="{{ $input }}">
                            </div>

                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-id-card text-sagara-blue"></i> {{ __('ui.business_license_no') }}</label>
                                <input name="business_license_no" value="{{ old('business_license_no') }}" placeholder="{{ __('ui.business_license_no') }}" required class="{{ $input }}">
                            </div>

                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-building-columns text-sagara-blue"></i> {{ __('ui.bank_account_no') }}</label>
                                <input name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="{{ __('ui.bank_account_no') }}" class="{{ $input }}">
                            </div>

                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-phone text-sagara-blue"></i> {{ __('ui.phone') }}</label>
                                <input name="phone_number" value="{{ old('phone_number') }}" placeholder="{{ __('ui.phone') }}" class="{{ $input }}">
                            </div>

                            <div class="{{ $box }} md:col-span-2">
                                <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sagara-blue"></i> {{ __('ui.email') }}</label>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required class="{{ $input }}">
                            </div>

                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-lock text-sagara-blue"></i> {{ __('ui.password') }}</label>
                                <input type="password" name="password" placeholder="{{ __('ui.password') }}" required class="{{ $input }}">
                            </div>

                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-lock text-sagara-blue"></i> {{ __('ui.password_confirm') }}</label>
                                <input type="password" name="password_confirmation" placeholder="{{ __('ui.password_confirm') }}" required class="{{ $input }}">
                            </div>
                        </div>

                        <button class="w-full py-4 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2 mt-2">
                            <i class="fa-solid fa-handshake"></i> {{ __('ui.register') }}
                        </button>
                    </form>

                    <p class="text-center text-xs text-slate-500 mt-6">
                        <a href="{{ route('login') }}" class="font-bold text-sagara-blue hover:underline">{{ __('ui.login') }}</a>
                    </p>
                </div>
            </div>
        </section>

    </div>
@endsection