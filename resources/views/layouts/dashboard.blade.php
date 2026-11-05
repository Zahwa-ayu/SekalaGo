<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @include('partials.head', ['pageTitle' => config('app.name') . ' - ' . __('ui.dashboard')])
    @stack('head')

    {{-- Pulihkan status sidebar (ciut/lebar) sebelum halaman tergambar, agar tidak berkedip --}}
    <script>
        try {
            if (localStorage.getItem('sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}
    </script>

    <style>
        /* Mode ciut hanya berlaku di desktop (lg ke atas). Di layar kecil sidebar berupa drawer. */
        @media (min-width: 1024px) {
            #sidebar { transition: width .2s ease; }
            html.sb-collapsed #sidebar { width: 5rem; }
            html.sb-collapsed #sidebar .sb-label,
            html.sb-collapsed #sidebar .sb-hide-collapsed { display: none; }
            html.sb-collapsed #sidebar .sb-item { justify-content: center; padding-left: 0; padding-right: 0; gap: 0; }
            html.sb-collapsed #sidebar .sb-brand { justify-content: center; padding-left: 0; padding-right: 0; }
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased selection:bg-sagara-teal selection:text-white">

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
    // Warna border dipilih per item (aktif = aksen teal, lainnya transparan)
    $linkBase = 'sb-item flex items-center gap-3 px-3 py-2.5 rounded-xl border-l-2 transition';
@endphp

<div class="min-h-screen lg:flex">

    {{-- Latar gelap saat sidebar (drawer) terbuka di layar kecil --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-slate-900 text-slate-300 border-r border-white/5
                  transform -translate-x-full transition-transform duration-200
                  lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:overflow-hidden">

        <div class="flex items-center justify-between h-20 border-b border-white/5 shrink-0 pr-3">
            <a href="{{ route('home') }}" class="sb-brand flex items-center gap-2.5 px-5 group min-w-0">
                <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg group-hover:scale-105 transition">
                    <i class="fa-solid fa-water text-slate-900 text-xl"></i>
                </div>
                <span class="sb-label text-xl font-extrabold tracking-tight text-white truncate">{{ config('app.name') }}</span>
            </a>

            {{-- Tombol tutup, hanya tampil di layar kecil --}}
            <button id="sidebar-close" type="button" class="lg:hidden p-2 text-slate-400 hover:text-white" aria-label="{{ __('ui.close_menu') }}">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 text-sm font-medium">
            <div class="space-y-1">
                @foreach ($menu as $item)
                    @php $active = request()->routeIs(...$item['active']); @endphp
                    <a href="{{ route($item['route']) }}"
                       title="{{ __($item['label']) }}"
                       class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white border-sagara-teal' : 'border-transparent hover:bg-white/5 hover:text-white' }}">
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center shrink-0 {{ $active ? 'text-sagara-teal' : '' }}"></i>
                        <span class="sb-label truncate">{{ __($item['label']) }}</span>
                    </a>
                @endforeach
            </div>

            @if (count($shortcuts))
                <div class="mt-6 pt-4 border-t border-white/5 space-y-1">
                    @foreach ($shortcuts as $item)
                        @php $active = request()->routeIs(...$item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           title="{{ __($item['label']) }}"
                           class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white border-sagara-teal' : 'border-transparent hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid {{ $item['icon'] }} w-5 text-center shrink-0"></i>
                            <span class="sb-label truncate">{{ __($item['label']) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </nav>

        <div class="px-3 py-4 border-t border-white/5 space-y-3 shrink-0 text-xs">
            <div class="sb-hide-collapsed px-1">
                @include('partials.preferences')
            </div>

            <a href="{{ route('home') }}" title="{{ __('ui.view_site') }}"
               class="{{ $linkBase }} border-transparent hover:bg-white/5 hover:text-white">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center shrink-0"></i>
                <span class="sb-label truncate">{{ __('ui.view_site') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="{{ __('ui.logout') }}"
                        class="w-full {{ $linkBase }} text-left border-transparent ring-1 ring-inset ring-white/15 hover:bg-white/10 hover:text-white">
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
                        class="p-2 -ml-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                        aria-label="{{ __('ui.open_menu') }}" aria-controls="sidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                @if ($context)
                    <div class="hidden sm:block text-sm font-bold text-slate-700 truncate">{{ $context }}</div>
                @endif
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:block font-semibold text-slate-600">{{ $authUser->email }}</span>
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-sagara-teal to-sky-400 text-slate-900 font-black flex items-center justify-center">
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
    })();
</script>
@stack('scripts')
</body>
</html>