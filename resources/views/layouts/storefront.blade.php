@php
    // Halaman yang latarnya terang (tanpa hero) mendapat navbar putih sejak awal.
    // Bisa lewat @section('solid_nav', '1') di view-nya, atau daftarkan nama route di bawah.
    $solidNav = \Illuminate\Support\Facades\View::hasSection('solid_nav')
        || request()->routeIs('cars.show', 'hotels.show', 'auth.login', 'corporate.dashboard',
        'bookings.*',
         'account.*');

    // Gaya link menu utama (Hotel / Mobil / FAQ / Daftar Mitra)
    $navLink = 'nav-link-text px-3 py-2 rounded-lg transition';
    $navOn   = 'nav-link-active';
    $navOff  = 'hover:bg-white/5 hover:text-sky-300';

    // Sosmed aktif untuk footer (diatur admin di Admin\SocialMediaController)
    $socialLinks = \App\Models\SocialMedia::where('status', 'aktif')->orderBy('order')->get();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @include('partials.head')
    @stack('head')

    <!-- FAVICON (Gunakan shortcut icon untuk file .ico) -->
    <link rel="shortcut icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">

    <style>
        /* Logo navbar: putih saat transparan, berwarna saat sudah scroll */
        .nav-logo-dark { display: none; }
        .is-scrolled .nav-logo-light { display: none; }
        .is-scrolled .nav-logo-dark { display: block; }

        /* Tombol bahasa/mata uang (ikon bola): garis tepi mengikuti state navbar */
        .pref-btn { border: 1px solid rgba(255, 255, 255, 0.25); }
        .is-scrolled .pref-btn { border-color: rgba(15, 23, 42, 0.15); }
        .pref-dd summary::-webkit-details-marker { display: none; }

        /* Link menu saat navbar putih */
        .is-scrolled .nav-link-text {
            color: #334155 !important;
        }
        .is-scrolled .nav-link-text:hover {
            color: #0284c7 !important;
        }
        .is-scrolled .nav-link-text:not(.nav-link-active):hover {
            background-color: #f1f5f9;
        }

        /* Menu aktif: terang di navbar transparan, biru muda di navbar putih (urutan harus setelah aturan di atas) */
        .nav-link-active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .is-scrolled .nav-link-active {
            background-color: #e0f2fe;
            color: #0284c7 !important;
        }

        /* Ikon sosmed footer: warna brand platform sebagai default, balik putih solid saat hover */
        .social-link {
            background-color: var(--sm-color);
            border: 1px solid var(--sm-color);
            color: #ffffff;
        }
        .social-link:hover {
            background-color: #ffffff;
            color: var(--sm-color);
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased selection:bg-sky-500 selection:text-white">

    {{-- NAVBAR: transparan lalu putih saat scroll. Halaman solid_nav (lihat atas file) langsung putih. --}}
    <nav id="main-navbar" 
         class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b
                {{ $solidNav
                    ? 'is-scrolled bg-white/95 backdrop-blur-md text-slate-900 shadow-md border-slate-200/80'
                    : 'bg-transparent text-white border-white/10' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo (kiri, sekaligus link ke Beranda): putih sebelum scroll, berwarna setelah scroll -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                    {{-- Sebelum scroll (navbar transparan) --}}
                    <img src="{{ asset('images/logo_home_white.png') }}"
                         alt="SekalaGo"
                         class="nav-logo-light h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">

                    {{-- Setelah scroll (navbar putih) --}}
                    <img src="{{ asset('images/logo_home.png') }}"
                         alt="SekalaGo"
                         class="nav-logo-dark h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                </a>

                <!-- Mobile Hamburger Button -->
                <button id="mobile-menu-btn" class="lg:hidden text-current p-2" aria-label="Menu" aria-controls="nav-menu" aria-expanded="false">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <!-- Navigation Items -->
                <div id="nav-menu"
                     class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-3 text-xs font-semibold
                            absolute lg:static top-16 left-0 right-0 {{ $solidNav ? 'bg-white' : 'bg-sky-900' }} lg:bg-transparent
                            px-4 pb-4 pt-2 lg:p-0 border-b border-white/10 lg:border-0">

                    {{-- MENU UTAMA (Beranda dihapus, sudah diwakili logo) --}}
                    <a href="{{ route('hotels.search') }}" class="{{ $navLink }} {{ request()->routeIs('hotels.*') ? $navOn : $navOff }}">
                        <i class="fa-solid fa-hotel mr-1"></i> {{ __('ui.tab_hotels') }}
                    </a>
                    <a href="{{ route('cars.home') }}" class="{{ $navLink }} {{ request()->routeIs('cars.*') ? $navOn : $navOff }}">
                        <i class="fa-solid fa-car mr-1"></i> {{ __('ui.tab_cars') }}
                    </a>
                    @if (Route::has('faq'))
                        <a href="{{ route('faq') }}" class="{{ $navLink }} {{ request()->routeIs('faq') ? $navOn : $navOff }}">
                            <i class="fa-solid fa-circle-question mr-1"></i> FAQ
                        </a>
                    @endif

                    {{-- DAFTAR MITRA (hanya tamu; hapus @guest kalau mau tampil untuk semua) --}}
                    @guest
                        <a href="{{ route('register.partner') }}" class="{{ $navLink }} {{ request()->routeIs('register.partner') ? $navOn : $navOff }}">
                            <i class="fa-solid fa-handshake mr-1"></i> {{ __('ui.register_partner') }}
                        </a>
                    @endguest

                    {{-- BAHASA & MATA UANG (satu tombol ikon bola) --}}
                    @include('partials.preferences')

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

                        {{-- PENGATURAN AKUN --}}
                        @if (Route::has('account.settings'))
                            <a href="{{ route('account.settings') }}" class="nav-link-text px-3 py-1.5 rounded-lg hover:bg-white/15 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-user-gear text-sky-400"></i> {{ __('ui.account_settings') }}
                            </a>
                        @endif

                        {{-- TOMBOL KELUAR / LOGOUT --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline-block ml-1">
                            @csrf
                            <button id="btn-logout" class="px-3.5 py-1.5 rounded-lg border {{ $solidNav ? 'border-slate-300 text-slate-700' : 'border-white/30' }} hover:bg-rose-600 hover:border-rose-600 hover:text-white transition flex items-center gap-1.5 font-bold shadow-sm">
                                <i class="fa-solid fa-right-from-bracket"></i> {{ __('ui.logout') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-link-text px-3 py-1.5 hover:opacity-80 transition">{{ __('ui.login') }}</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-center font-bold text-white bg-sky-500 hover:bg-sky-600 rounded-lg shadow-md shadow-sky-500/20 transition">{{ __('ui.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @include('partials.flash')

    {{-- Navbar bersifat fixed (h-16). Di halaman solid_nav tidak ada hero yang "mengisi" ruang itu, jadi beri jarak atas. --}}
    <main class="min-h-screen {{ $solidNav ? 'pt-16' : '' }}">
        @yield('content')
    </main>

    {{-- FOOTER SKY-950 SOLID (biru tua, senada dengan hero) --}}
    <footer class="bg-sky-950 text-sky-100/80 text-xs border-t border-sky-900 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div class="max-w-sm space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                        <img src="{{ asset('images/logo_home_white.png') }}" 
                            alt="SekalaGo" 
                            class="h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                    </a>
                    <p class="leading-relaxed text-sky-200/70">{{ __('ui.footer_about') }}</p>

                    {{-- SOSIAL MEDIA (dari database, hanya yang berstatus aktif) --}}
                    @if ($socialLinks->isNotEmpty())
                        <div class="pt-2">
                            <h4 class="text-white font-bold text-xs mb-3">{{ __('ui.footer_social') }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($socialLinks as $social)
                                    <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
                                       title="{{ $social->platform }}" aria-label="{{ $social->platform }}"
                                       style="--sm-color: {{ $social->color ?: '#0ea5e9' }}"
                                       class="social-link w-9 h-9 rounded-xl flex items-center justify-center hover:-translate-y-0.5 transition">
                                        <i class="{{ $social->icon_class }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-10">
                    <div>
                        <h4 class="text-white font-bold text-xs mb-3">{{ __('ui.footer_services') }}</h4>
                        <ul class="space-y-2 text-sky-200/70">
                            <li class="hover:text-white transition cursor-pointer">{{ __('ui.footer_service_hotel') }}</li>
                            <li class="hover:text-white transition cursor-pointer">{{ __('ui.footer_service_selfdrive') }}</li>
                            <li class="hover:text-white transition cursor-pointer">{{ __('ui.footer_service_driver') }}</li>
                            <li class="hover:text-white transition cursor-pointer">{{ __('ui.footer_service_combo') }}</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs mb-3">{{ __('ui.footer_partnership') }}</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('register.partner') }}" class="hover:text-white transition">{{ __('ui.register_partner') }}</a></li>
                            {{-- Link FAQ baru muncul jika route 'faq' sudah terdaftar --}}
                            @if (Route::has('faq'))
                                <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-5 border-t border-sky-900 text-sky-300/60 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div>
                    © {{ date('Y') }} {{ config('app.name') }}. {{ __('ui.footer_rights') }}
                </div>
                <div class="flex gap-4 text-xs">
                    <span class="hover:text-white cursor-pointer">{{ __('ui.footer_terms') }}</span>
                    <span class="hover:text-white cursor-pointer">{{ __('ui.footer_privacy') }}</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- SCRIPT CONTROL SCROLL NAVBAR --}}
    <script>
        (function () {
            const nav = document.getElementById('main-navbar');
            const btnLogout = document.getElementById('btn-logout');
            const btnMobile = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('nav-menu');

            // true = halaman meminta navbar putih permanen (tidak transparan)
            const alwaysSolid = @json($solidNav);

            function updateNavbar() {
                const scrolled = alwaysSolid || window.scrollY > 20;

                if (scrolled) {
                    nav.classList.add('is-scrolled');
                    nav.classList.remove('bg-transparent', 'text-white', 'border-white/10');
                    nav.classList.add('bg-white/95', 'backdrop-blur-md', 'text-slate-900', 'shadow-md', 'border-slate-200/80');

                    if (btnLogout) {
                        btnLogout.classList.remove('border-white/30');
                        btnLogout.classList.add('border-slate-300', 'text-slate-700');
                    }

                    menu.classList.remove('bg-sky-900');
                    menu.classList.add('bg-white');
                } else {
                    nav.classList.remove('is-scrolled');
                    nav.classList.add('bg-transparent', 'text-white', 'border-white/10');
                    nav.classList.remove('bg-white/95', 'backdrop-blur-md', 'text-slate-900', 'shadow-md', 'border-slate-200/80');

                    if (btnLogout) {
                        btnLogout.classList.add('border-white/30');
                        btnLogout.classList.remove('border-slate-300', 'text-slate-700');
                    }

                    menu.classList.add('bg-sky-900');
                    menu.classList.remove('bg-white');
                }
            }

            window.addEventListener('scroll', updateNavbar);

            // Sinkronkan state navbar saat halaman dimuat/refresh di posisi yang sudah ter-scroll
            updateNavbar();

            btnMobile.addEventListener('click', () => {
                // Saat terbuka, 'flex' wajib ditambahkan agar flex-col berlaku di mobile.
                // Tanpa display:flex, isi menu tampil inline dan saling bersebelahan.
                const open = menu.classList.toggle('flex');
                menu.classList.toggle('hidden', !open);
                btnMobile.setAttribute('aria-expanded', String(open));
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>