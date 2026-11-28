@php
    // Halaman yang latarnya terang (tanpa hero) mendapat navbar putih sejak awal.
    // Bisa lewat @section('solid_nav', '1') di view-nya, atau daftarkan nama route di bawah.
    $solidNav = \Illuminate\Support\Facades\View::hasSection('solid_nav')
        || request()->routeIs('cars.show', 'hotels.show', 'auth.login', 'corporate.dashboard', 'register.corporate', 
        'bookings.*', 'password.*', 'car-bookings.*', 'issues.create', 'issues.index', 'notifications.index',
         'account.*');

    // Gaya link menu utama (Hotel / Mobil / FAQ / Bisnis)
    $navLink = 'nav-link-text px-3 py-2 rounded-lg transition';
    $navOn   = 'nav-link-active';
    $navOff  = 'hover:bg-white/5 hover:text-sky-300';

    // Sosmed aktif untuk footer (diatur admin di Admin\SocialMediaController)
    $socialLinks = \App\Models\SocialMedia::where('status', 'aktif')->orderBy('order')->get();

    // Kontak aktif untuk footer (diatur admin di Admin\ContactInfoController)
    $contacts = \App\Models\ContactInfo::active()->orderBy('order')->orderBy('id')->get();

    // Halaman statis (Tentang Kami, Syarat, Privasi) yang diberi centang "tampil di footer"
    $footerPages = \App\Models\Page::footer()->get();
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

        /* Dropdown Bisnis: sembunyikan panah bawaan <details> */
        .biz-dd summary::-webkit-details-marker { display: none; }

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

        /* Dropdown Akun: sembunyikan panah bawaan <details> */
        .acct-dd summary::-webkit-details-marker { display: none; }

        /* Lonceng notifikasi: ikut gelap saat navbar putih (isi dropdown tidak terpengaruh) */
        .is-scrolled #notif-toggle { color: #334155; }
        .is-scrolled #notif-toggle:hover { color: #0284c7; background-color: #f1f5f9; }

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
                         class="nav-logo-light h-11 w-auto max-w-[180px] object-contain group-hover:scale-105 transition">

                    {{-- Setelah scroll (navbar putih) --}}
                    <img src="{{ asset('images/logo_home.png') }}"
                         alt="SekalaGo"
                         class="nav-logo-dark h-11 w-auto max-w-[180px] object-contain group-hover:scale-105 transition">
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

                    {{-- BISNIS (hanya tamu): dropdown ke pendaftaran Mitra & Corporate (B2B) --}}
                    @guest
                        @php $bizActive = request()->routeIs('register.partner', 'register.corporate'); @endphp
                        <details class="biz-dd relative">
                            <summary class="{{ $navLink }} {{ $bizActive ? $navOn : $navOff }} cursor-pointer list-none flex items-center gap-1">
                                <i class="fa-solid fa-briefcase mr-1"></i> {{ __('ui.business') }}
                                <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                            </summary>

                            <div class="lg:absolute lg:right-0 lg:top-full lg:mt-2 lg:w-60 mt-1 z-50
                                        bg-white text-slate-700 rounded-xl border border-slate-200 shadow-lg p-1.5">
                                <a href="{{ route('register.partner') }}"
                                   class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-600 transition">
                                    <i class="fa-solid fa-handshake mt-0.5 text-sky-500"></i>
                                    <span>
                                        <span class="block font-bold">{{ __('ui.register_partner') }}</span>
                                        <span class="block font-normal text-[11px] text-slate-500">{{ __('ui.register_partner_desc') }}</span>
                                    </span>
                                </a>
                                <a href="{{ route('register.corporate') }}"
                                   class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-600 transition">
                                    <i class="fa-solid fa-building mt-0.5 text-sky-500"></i>
                                    <span>
                                        <span class="block font-bold">{{ __('ui.register_corporate') }}</span>
                                        <span class="block font-normal text-[11px] text-slate-500">{{ __('ui.register_corporate_desc') }}</span>
                                    </span>
                                </a>
                            </div>
                        </details>
                    @endguest

                    {{-- Pemisah antara menu utama dan area akun (hanya desktop) --}}
                    <span class="hidden lg:block w-px h-5 bg-current opacity-20" aria-hidden="true"></span>

                    {{-- BAHASA & MATA UANG (satu tombol ikon bola) --}}
                    @include('partials.preferences')

                    {{-- DASHBOARD: tetap di luar karena pintu utama kerja partner, admin, dan corporate --}}
                    @auth
                        @if (auth()->user()->user_type !== 'customer')
                            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="{{ $navLink }} {{ request()->routeIs(auth()->user()->dashboardRoute()) ? $navOn : $navOff }}">
                                <i class="fa-solid fa-gauge mr-1"></i> {{ __('ui.dashboard') }}
                            </a>
                        @endif
                    @endauth

                    @auth
                        {{-- LONCENG NOTIFIKASI --}}
                        @include('partials.notification-bell', ['tone' => 'dark'])

                        {{-- DROPDOWN AKUN: Pesanan Saya, Pengaturan Akun, Keluar --}}
                        <details class="acct-dd relative">
                            <summary class="{{ $navLink }} {{ request()->routeIs('account.*', 'bookings.*') ? $navOn : $navOff }} cursor-pointer list-none flex items-center gap-1">
                                <i class="fa-solid fa-circle-user text-xl mr-1"></i> {{ __('ui.menu_account') }}
                                <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                            </summary>

                            <div class="lg:absolute lg:right-0 lg:top-full lg:mt-2 lg:w-52 mt-1 z-50
                                        bg-white text-slate-700 rounded-xl border border-slate-200 shadow-lg p-1.5">
                                @if (in_array(auth()->user()->user_type, ['customer', 'corporate']))
                                    <a href="{{ route('bookings.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-600 transition {{ request()->routeIs('bookings.*') ? 'bg-sky-50 text-sky-600' : '' }}">
                                        <i class="fa-solid fa-receipt w-4 text-center text-sky-500"></i> {{ __('ui.my_bookings') }}
                                    </a>
                                @endif

                                {{-- Finance tidak perlu Pengaturan Akun di sini: aksesnya lewat sidebar Dashboard --}}
                                @if (Route::has('account.settings') && ! (auth()->user()->user_type === 'corporate' && auth()->user()->companyUser?->isFinance()))
                                    <a href="{{ route('account.settings') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-600 transition">
                                        <i class="fa-solid fa-user-gear w-4 text-center text-sky-500"></i> {{ __('ui.account_settings') }}
                                    </a>
                                @endif

                                {{-- Garis pemisah hanya kalau ada menu di atas tombol Keluar --}}
                                <form method="POST" action="{{ route('logout') }}" class="[&:not(:first-child)]:border-t [&:not(:first-child)]:border-slate-100 [&:not(:first-child)]:mt-1 [&:not(:first-child)]:pt-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-rose-600 hover:bg-rose-50 transition font-bold">
                                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> {{ __('ui.logout') }}
                                    </button>
                                </form>
                            </div>
                        </details>
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
    <footer class="bg-sky-950 text-sky-100/80 text-xs border-t border-sky-900 {{ \Illuminate\Support\Facades\View::hasSection('flush_footer') ? '' : 'mt-20' }}">
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
                <div class="grid grid-cols-2 md:grid-cols-3 gap-10">
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
                            <li><a href="{{ route('register.corporate') }}" class="hover:text-white transition">{{ __('ui.register_corporate') }}</a></li>
                            {{-- Link FAQ baru muncul jika route 'faq' sudah terdaftar --}}
                            @if (Route::has('faq'))
                                <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a></li>
                            @endif
                        </ul>
                    </div>

                    {{-- HUBUNGI KAMI (dari database, hanya yang berstatus aktif) --}}
                    @if ($contacts->isNotEmpty())
                        <div class="col-span-2 md:col-span-1 max-w-xs">
                            <h4 class="text-white font-bold text-xs mb-3">{{ __('ui.contact_us') }}</h4>
                            <ul class="space-y-2.5 text-sky-200/70">
                                @foreach ($contacts as $contact)
                                    <li class="flex items-start gap-2.5">
                                        <i class="{{ $contact->icon }} w-4 mt-0.5 text-center text-sky-400 shrink-0"></i>
                                        <span class="leading-relaxed min-w-0">
                                            @if ($contact->label)
                                                <span class="block text-[11px] text-sky-300/60">{{ $contact->label }}</span>
                                            @endif
                                            @if ($contact->href)
                                                <a href="{{ $contact->href }}"
                                                   @if (str_starts_with($contact->href, 'http')) target="_blank" rel="noopener noreferrer" @endif
                                                   class="hover:text-white transition break-words">{{ $contact->value }}</a>
                                            @else
                                                <span class="break-words">{{ $contact->value }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-10 pt-5 border-t border-sky-900 text-sky-300/60 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div>
                    © {{ date('Y') }} {{ config('app.name') }}. {{ __('ui.footer_rights') }}
                </div>
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs">
                    @forelse ($footerPages as $footerPage)
                        <a href="{{ route('pages.show', $footerPage->slug) }}" class="hover:text-white transition">{{ $footerPage->title }}</a>
                    @empty
                        {{-- Cadangan sampai admin membuat halaman pertama --}}
                        <span class="hover:text-white cursor-pointer">{{ __('ui.footer_terms') }}</span>
                        <span class="hover:text-white cursor-pointer">{{ __('ui.footer_privacy') }}</span>
                    @endforelse

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

            // Dropdown Akun: tutup saat klik di luar atau tekan Esc
            const acctDd = document.querySelector('.acct-dd');
            if (acctDd) {
                document.addEventListener('click', (e) => { if (!acctDd.contains(e.target)) acctDd.open = false; });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') acctDd.open = false; });
            }

            // Dropdown Bisnis: tutup saat klik di luar atau tekan Esc
            const bizDd = document.querySelector('.biz-dd');
            if (bizDd) {
                document.addEventListener('click', (e) => { if (!bizDd.contains(e.target)) bizDd.open = false; });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') bizDd.open = false; });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>