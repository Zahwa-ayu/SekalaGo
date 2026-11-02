<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    @include('partials.head', ['pageTitle' => config('app.name') . ' - ' . __('ui.dashboard')])
    @stack('head')
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
    $linkBase = 'flex items-center gap-3 px-3 py-2.5 rounded-xl transition';
@endphp

<div class="min-h-screen lg:flex">

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-sagara-dark text-slate-300 border-r border-white/10
                  transform -translate-x-full transition-transform duration-200
                  lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:shrink-0">

        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-5 h-20 border-b border-white/10 shrink-0 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg group-hover:scale-105 transition">
                <i class="fa-solid fa-water text-sagara-dark text-xl"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tight text-white">{{ config('app.name') }}</span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 text-sm font-medium">
            <div class="space-y-1">
                @foreach ($menu as $item)
                    @php $active = request()->routeIs(...$item['active']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white shadow-inner' : 'hover:bg-white/5 hover:text-sky-300' }}">
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center {{ $active ? 'text-sagara-teal' : '' }}"></i>
                        <span>{{ __($item['label']) }}</span>
                    </a>
                @endforeach
            </div>

            @if (count($shortcuts))
                <div class="mt-6 pt-4 border-t border-white/10 space-y-1">
                    @foreach ($shortcuts as $item)
                        @php $active = request()->routeIs(...$item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="{{ $linkBase }} {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-sky-300' }}">
                            <i class="fa-solid {{ $item['icon'] }} w-5 text-center"></i>
                            <span>{{ __($item['label']) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </nav>

        <div class="px-3 py-4 border-t border-white/10 space-y-3 shrink-0 text-xs">
            <div class="px-1">
                @include('partials.preferences')
            </div>

            <a href="{{ route('home') }}" class="{{ $linkBase }} hover:bg-white/5 hover:text-sky-300">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i>
                <span>{{ __('ui.view_site') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full {{ $linkBase }} text-left border border-white/20 hover:bg-white/10">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>{{ __('ui.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- AREA KONTEN --}}
    <div class="flex-1 min-w-0 flex flex-col">

        <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button id="sidebar-open" class="lg:hidden text-slate-600 hover:text-sagara-blue p-2" aria-label="{{ __('ui.open_menu') }}">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                @if ($context)
                    <div class="hidden sm:block text-sm font-bold text-slate-700">{{ $context }}</div>
                @endif
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:block font-semibold text-slate-600">{{ $authUser->email }}</span>
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-sagara-teal to-sky-400 text-sagara-dark font-black flex items-center justify-center">
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
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar(open) {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);
    }

    document.getElementById('sidebar-open').addEventListener('click', () => toggleSidebar(true));
    overlay.addEventListener('click', () => toggleSidebar(false));
</script>
@stack('scripts')
</body>
</html>