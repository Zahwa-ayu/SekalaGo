<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @include('partials.head')
    @stack('head')

    <!-- FAVICON (Gunakan shortcut icon untuk file .ico) -->
    <link rel="shortcut icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">

    <style>
        /* Hilangkan box background & border bawaan dari partial preferences */
        .pref-wrapper, 
        .pref-wrapper *, 
        .pref-wrapper form, 
        .pref-wrapper div, 
        .pref-wrapper select, 
        .pref-wrapper button {
            background-color: transparent !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }

        .pref-wrapper option {
            color: #0f172a !important;
            background-color: #ffffff !important;
        }

        .is-scrolled .pref-wrapper, 
        .is-scrolled .pref-wrapper *, 
        .is-scrolled .pref-wrapper select, 
        .is-scrolled .pref-wrapper button {
            color: #0f172a !important;
            border-color: rgba(15, 23, 42, 0.15) !important;
        }

        .is-scrolled .nav-link-text {
            color: #334155 !important;
        }
        .is-scrolled .nav-link-text:hover {
            color: #0284c7 !important;
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased selection:bg-sky-500 selection:text-white">

    {{-- NAVBAR TRANSPARENT TO WHITE ON SCROLL --}}
    <nav id="main-navbar" 
         class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent text-white border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo (Menggunakan Logo Baru dari public/images) -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                    <img src="{{ asset('images/logo_home.png') }}" 
                        alt="SekalaGo" 
                        class="h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                </a>

                

                <!-- Mobile Hamburger Button -->
                <button id="mobile-menu-btn" class="lg:hidden text-current p-2" aria-label="Menu" aria-controls="nav-menu" aria-expanded="false">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <!-- Navigation Items -->
                <div id="nav-menu"
                     class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-3 text-xs font-semibold
                            absolute lg:static top-16 left-0 right-0 bg-slate-900 lg:bg-transparent
                            px-4 pb-4 pt-2 lg:p-0 border-b border-white/10 lg:border-0">

                    {{-- PREFERENCES (BAHASA & MATA UANG) --}}
                    <div class="pref-wrapper flex items-center gap-1.5">
                        @include('partials.preferences')
                    </div>

                    @auth
                        @if (in_array(auth()->user()->user_type, ['customer', 'corporate']))
                            <a href="{{ route('bookings.index') }}" class="nav-link-text px-3 py-1.5 rounded-lg hover:bg-white/15 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-receipt text-sky-400"></i> {{ __('ui.my_bookings') }}
                            </a>
                        @endif

                        @if (auth()->user()->user_type !== 'customer')
                            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="nav-link-text px-3 py-1.5 rounded-lg hover:bg-white/15 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-gauge text-sky-400"></i> {{ __('ui.dashboard') }}
                            </a>
                        @endif

                        {{-- TOMBOL KELUAR / LOGOUT --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline-block ml-1">
                            @csrf
                            <button id="btn-logout" class="px-3.5 py-1.5 rounded-lg border border-white/30 hover:bg-rose-600 hover:border-rose-600 hover:text-white transition flex items-center gap-1.5 font-bold shadow-sm">
                                <i class="fa-solid fa-right-from-bracket"></i> {{ __('ui.logout') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-link-text px-3 py-1.5 hover:opacity-80 transition">{{ __('ui.login') }}</a>
                        <a href="{{ route('register.partner') }}" class="nav-link-text px-3 py-1.5 hover:opacity-80 transition">{{ __('ui.register_partner') }}</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-center font-bold text-white bg-sky-500 hover:bg-sky-600 rounded-lg shadow-md shadow-sky-500/20 transition">{{ __('ui.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @include('partials.flash')

    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- FOOTER SLATE-900 SOLID --}}
    <footer class="bg-slate-900 text-slate-300 text-xs border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div class="max-w-sm space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo_home.ico') }}" alt="SekalaGo" class="w-8 h-8 object-contain">
                        <span class="text-lg font-black text-white tracking-tight">{{ config('app.name') }}</span>
                    </a>
                    <p class="leading-relaxed text-slate-400">Platform reservasi hotel, villa, dan sewa kendaraan terpercaya Nusantara. Pilihan lengkap, aman, dan harga transparan.</p>
                </div>
                <div class="grid grid-cols-2 gap-10">
                    <div>
                        <h4 class="text-white font-bold text-xs mb-3">Layanan Kami</h4>
                        <ul class="space-y-2 text-slate-400">
                            <li class="hover:text-sky-400 transition cursor-pointer">Reservasi Hotel & Resort</li>
                            <li class="hover:text-sky-400 transition cursor-pointer">Sewa Mobil Lepas Kunci</li>
                            <li class="hover:text-sky-400 transition cursor-pointer">Rental Mobil + Sopir</li>
                            <li class="hover:text-sky-400 transition cursor-pointer">Paket Combo Bundling</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs mb-3">Kemitraan</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('register.partner') }}" class="hover:text-sky-400 transition">{{ __('ui.register_partner') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-5 border-t border-slate-800 text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div>
                    © {{ date('Y') }} {{ config('app.name') }}. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="flex gap-4 text-xs">
                    <span class="hover:text-slate-300 cursor-pointer">Syarat & Ketentuan</span>
                    <span class="hover:text-slate-300 cursor-pointer">Kebijakan Privasi</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- SCRIPT CONTROL SCROLL NAVBAR --}}
    <script>
        (function () {
            const nav = document.getElementById('main-navbar');
            const brand = document.getElementById('nav-brand');
            const btnLogout = document.getElementById('btn-logout');
            const btnMobile = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('nav-menu');

            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    nav.classList.add('is-scrolled');
                    nav.classList.remove('bg-transparent', 'text-white', 'border-white/10');
                    nav.classList.add('bg-white/95', 'backdrop-blur-md', 'text-slate-900', 'shadow-md', 'border-slate-200/80');
                    brand.classList.remove('text-white');
                    brand.classList.add('text-slate-900');

                    if(btnLogout) {
                        btnLogout.classList.remove('border-white/30');
                        btnLogout.classList.add('border-slate-300', 'text-slate-700');
                    }

                    menu.classList.remove('bg-slate-900');
                    menu.classList.add('bg-white');
                } else {
                    nav.classList.remove('is-scrolled');
                    nav.classList.add('bg-transparent', 'text-white', 'border-white/10');
                    nav.classList.remove('bg-white/95', 'backdrop-blur-md', 'text-slate-900', 'shadow-md', 'border-slate-200/80');
                    brand.classList.add('text-white');
                    brand.classList.remove('text-slate-900');

                    if(btnLogout) {
                        btnLogout.classList.add('border-white/30');
                        btnLogout.classList.remove('border-slate-300', 'text-slate-700');
                    }

                    menu.classList.add('bg-slate-900');
                    menu.classList.remove('bg-white');
                }
            });

            btnMobile.addEventListener('click', () => {
                const hidden = menu.classList.toggle('hidden');
                btnMobile.setAttribute('aria-expanded', String(!hidden));
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>