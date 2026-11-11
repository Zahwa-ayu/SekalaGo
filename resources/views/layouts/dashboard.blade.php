<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @include('partials.head', ['pageTitle' => config('app.name') . ' - ' . __('ui.dashboard')])
    @stack('head')

    <!-- FAVICON LOGO SEKALA GO -->
    <link rel="shortcut icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo_home.png') }}" type="image/x-icon">

    {{-- Pulihkan status sidebar (ciut/lebar) sebelum halaman tergambar, agar tidak berkedip --}}
    <script>
        try {
            if (localStorage.getItem('sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}
    </script>

    <style>
        /* Default: Icon disembunyikan, Logo Full ditampilkan */
        .logo-icon { display: none; }
        .logo-full { display: block; }

        /* ----------------------------------------------------
           SUB-MENU GAYA POHON: garis vertikal + siku melengkung
           Setiap anak menggambar siku (::before) dan lanjutan garis (::after).
           Anak terakhir tidak punya lanjutan, jadi garis berhenti di siku.
           ---------------------------------------------------- */
        .sb-tree { margin-left: 1.375rem; padding-left: .75rem; }
        .sb-tree-item { position: relative; padding-top: .125rem; padding-bottom: .125rem; }
        .sb-tree-item::before {
            content: '';
            position: absolute;
            left: -.75rem;
            top: 0;
            width: .75rem;
            height: 50%;
            border-left: 1px solid rgba(255, 255, 255, .14);
            border-bottom: 1px solid rgba(255, 255, 255, .14);
            border-bottom-left-radius: .5rem;
        }
        .sb-tree-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -.75rem;
            top: 50%;
            bottom: 0;
            border-left: 1px solid rgba(255, 255, 255, .14);
        }

        /* Ikon anak hanya dipakai saat sidebar ciut (di mode lebar hanya teks) */
        .sb-child-icon { display: none; }

        /* Mode ciut hanya berlaku di desktop (lg ke atas). Di layar kecil sidebar berupa drawer. */
        @media (min-width: 1024px) {
            #sidebar { transition: width .2s ease; }
            html.sb-collapsed #sidebar { width: 5rem; }
            html.sb-collapsed #sidebar .sb-label,
            html.sb-collapsed #sidebar .sb-hide-collapsed { display: none; }
            html.sb-collapsed #sidebar .sb-item { justify-content: center; padding-left: 0; padding-right: 0; gap: 0; }
            html.sb-collapsed #sidebar .sb-brand { justify-content: center; padding-left: 0; padding-right: 0; }

            /* Saat ciut, grup tidak bisa dibuka-tutup: semua sub-menu tampil sebagai ikon */
            html.sb-collapsed #sidebar .sb-group-list { display: block !important; margin-top: .25rem; }

            /* Saat ciut: garis pohon dibuang, ikon anak ditampilkan */
            html.sb-collapsed #sidebar .sb-tree { margin-left: 0; padding-left: 0; }
            html.sb-collapsed #sidebar .sb-tree-item::before,
            html.sb-collapsed #sidebar .sb-tree-item::after { display: none; }
            html.sb-collapsed #sidebar .sb-child-icon { display: inline-block; }

            /* SAAT SIDEBAR CIUT: Sembunyikan Logo Full, Tampilkan Icon */
            html.sb-collapsed .logo-full { display: none; }
            html.sb-collapsed .logo-icon { display: block; }
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased selection:bg-sky-500 selection:text-white">

@php
    $authUser  = auth()->user();
    $menu      = \App\Support\DashboardMenu::for($authUser);
    $shortcuts = \App\Support\DashboardMenu::shortcuts($authUser);
    $context   = match ($authUser->user_type) {
        'admin'     => config('app.name') . ' Admin',
        'partner'   => $authUser->partner?->company_name,
        'corporate' => $authUser->companyUser?->company?->company_name,
        default     => null,
    };
    // Warna border aksen menggunakan sky-500 untuk SekalaGo
    $linkBase = 'sb-item flex items-center gap-3 px-3 py-2.5 rounded-xl border-l-2 transition';
@endphp

<div class="min-h-screen lg:flex">

    {{-- Latar gelap saat sidebar (drawer) terbuka di layar kecil --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

    {{-- SIDEBAR (Slate 900 Solid) --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-slate-900 text-slate-300 border-r border-white/5
                  transform -translate-x-full transition-transform duration-200
                  lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:overflow-hidden">

        <div class="flex items-center justify-center h-20 border-b border-white/5 shrink-0 relative">
            <!-- Brand Logo SekalaGo untuk Sidebar -->
            <a href="{{ route('home') }}" class="sb-brand flex items-center justify-center group w-full px-5 lg:px-0">

                <!-- 1. Ikon Saja (Muncul saat sidebar menciut) -->
                <img src="{{ asset('images/logo_sekalago.ico') }}"
                     alt="Icon"
                     class="logo-icon w-9 h-9 object-contain group-hover:scale-105 transition mx-auto">

                <!-- 2. Logo Lengkap (Muncul saat sidebar lebar/mobile) -->
                <img src="{{ asset('images/logo_home_white.png') }}"
                     alt="SekalaGo"
                     class="logo-full h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition mx-auto">

            </a>

            {{-- Tombol tutup, hanya tampil di layar kecil (posisi absolute agar tidak menggeser logo) --}}
            <button id="sidebar-close" type="button" class="lg:hidden absolute right-3 p-2 text-slate-400 hover:text-white" aria-label="{{ __('ui.close_menu') }}">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 text-sm font-medium">
            <div class="space-y-1">
                @foreach ($menu as $item)
                    @if ($item['type'] === 'group')
                        {{-- GRUP: bisa dibuka-tutup; otomatis terbuka jika salah satu halamannya aktif --}}
                        @php $open = request()->routeIs(...$item['active']); @endphp

                        <div data-sb-group @if ($open) data-sb-open="1" @endif>
                            <button type="button" data-sb-toggle aria-expanded="{{ $open ? 'true' : 'false' }}"
                                    title="{{ __($item['label']) }}"
                                    class="w-full {{ $linkBase }} text-left border-transparent hover:bg-white/5 hover:text-white {{ $open ? 'text-white' : '' }}">
                                <i class="fa-solid {{ $item['icon'] }} w-5 text-center shrink-0"></i>
                                <span class="sb-label truncate flex-1">{{ __($item['label']) }}</span>
                                <i data-sb-chevron class="sb-hide-collapsed fa-solid fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200 {{ $open ? 'rotate-180' : '' }}"></i>
                            </button>

                            {{-- Sub-menu bergaya pohon: hanya teks, dengan garis penghubung di kiri --}}
                            <div class="sb-group-list sb-tree mt-1 {{ $open ? '' : 'hidden' }}">
                                @foreach ($item['children'] as $child)
                                    @php $active = request()->routeIs(...$child['active']); @endphp
                                    <div class="sb-tree-item">
                                        <a href="{{ route($child['route']) }}"
                                           title="{{ __($child['label']) }}"
                                           class="{{ $linkBase }} py-2 text-[13px] {{ $active ? 'bg-white/10 text-white font-semibold border-sky-500' : 'border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                            <i class="fa-solid {{ $child['icon'] }} sb-child-icon w-5 text-center shrink-0 {{ $active ? 'text-sky-400' : '' }}"></i>
                                            <span class="sb-label truncate">{{ __($child['label']) }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php $active = request()->routeIs(...$item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           title="{{ __($item['label']) }}"
                           class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white border-sky-500' : 'border-transparent hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid {{ $item['icon'] }} w-5 text-center shrink-0 {{ $active ? 'text-sky-400' : '' }}"></i>
                            <span class="sb-label truncate">{{ __($item['label']) }}</span>
                        </a>
                    @endif
                @endforeach
            </div>

            @if (count($shortcuts))
                <div class="mt-6 pt-4 border-t border-white/5 space-y-1">
                    @foreach ($shortcuts as $item)
                        @php $active = request()->routeIs(...$item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           title="{{ __($item['label']) }}"
                           class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white border-sky-500' : 'border-transparent hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid {{ $item['icon'] }} w-5 text-center shrink-0"></i>
                            <span class="sb-label truncate">{{ __($item['label']) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </nav>

        <div class="px-3 py-4 border-t border-white/5 space-y-3 shrink-0 text-xs">
            <a href="{{ route('home') }}" title="{{ __('ui.view_site') }}"
               class="{{ $linkBase }} border-transparent hover:bg-white/5 hover:text-white">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center shrink-0"></i>
                <span class="sb-label truncate">{{ __('ui.view_site') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="{{ __('ui.logout') }}"
                        class="w-full {{ $linkBase }} text-left border-transparent ring-1 ring-inset ring-white/15 hover:bg-white/10 hover:text-white hover:border-rose-500 hover:text-rose-400 transition-colors">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center shrink-0"></i>
                    <span class="sb-label truncate">{{ __('ui.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- AREA KONTEN --}}
    <div class="flex-1 min-w-0 flex flex-col">

        <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Tombol "=": drawer di layar kecil, ciut/lebar di desktop --}}
                <button id="sidebar-toggle" type="button"
                        class="p-2 -ml-2 rounded-lg text-slate-600 hover:text-sky-600 hover:bg-sky-50 transition"
                        aria-label="{{ __('ui.open_menu') }}" aria-controls="sidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                @if ($context)
                    <div class="hidden sm:block text-sm font-bold text-slate-700 truncate">{{ $context }}</div>
                @endif
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:block font-semibold text-slate-600">{{ $authUser->email }}</span>
                <!-- Avatar Profil: Solid Sky Blue -->
                <div class="w-9 h-9 rounded-full bg-sky-500 text-white font-black flex items-center justify-center shadow-md shadow-sky-500/20">
                    {{ strtoupper(substr($authUser->email, 0, 1)) }}
                </div>
            </div>
        </header>

        @include('partials.flash')

        <main class="flex-1">
            @yield('content')
        </main>
    </div>
</div>

<script>
    (function () {
        const root    = document.documentElement;
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle  = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');
        const desktop = window.matchMedia('(min-width: 1024px)');

        // Layar kecil: buka/tutup drawer
        function setDrawer(open) {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', String(open));
        }

        // Desktop: ciutkan/lebarkan sidebar dan ingat pilihannya
        function setCollapsed(collapsed) {
            root.classList.toggle('sb-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            try { localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
        }

        toggle.addEventListener('click', () => {
            if (desktop.matches) {
                setCollapsed(!root.classList.contains('sb-collapsed'));
            } else {
                setDrawer(sidebar.classList.contains('-translate-x-full'));
            }
        });

        overlay.addEventListener('click', () => setDrawer(false));
        closeBtn.addEventListener('click', () => setDrawer(false));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setDrawer(false); });

        // Saat pindah ke desktop, pastikan drawer tertutup dan state aria sinkron
        desktop.addEventListener('change', (e) => {
            setDrawer(false);
            toggle.setAttribute('aria-expanded', String(e.matches ? !root.classList.contains('sb-collapsed') : false));
        });
        toggle.setAttribute('aria-expanded', String(desktop.matches ? !root.classList.contains('sb-collapsed') : false));

        // Grup menu: buka/tutup sub-menu dan putar panahnya
        document.querySelectorAll('[data-sb-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const list = button.parentElement.querySelector('.sb-group-list');
                const open = list.classList.toggle('hidden') === false;

                button.setAttribute('aria-expanded', String(open));
                button.querySelector('[data-sb-chevron]')?.classList.toggle('rotate-180', open);
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>