<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Jelajah Surga Bahari Nusantara</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        sagara: {
                            dark: '#031E38', navy: '#0b3c5d', blue: '#0077b6',
                            teal: '#00b4d8', sky: '#caf0f8', gold: '#f4a261', coral: '#e76f51',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hero-bg { background: linear-gradient(180deg, #031e38 0%, #084c77 55%, #0077b6 85%, #e0f2fe 100%); }
        .glass-card { background: rgba(255,255,255,.95); backdrop-filter: blur(12px); }
    </style>
    @stack('head')
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased selection:bg-sagara-teal selection:text-white">

    {{-- NAVBAR --}}
    <nav class="sticky top-0 z-50 bg-sagara-dark/95 backdrop-blur-md border-b border-white/10 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg group-hover:scale-105 transition">
                        <i class="fa-solid fa-water text-sagara-dark text-xl"></i>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight">{{ config('app.name') }}</span>
                </a>

                <button id="mobile-menu-btn" class="lg:hidden text-slate-300 hover:text-white p-2" aria-label="Menu">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>

                <div id="nav-menu"
                     class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-1 lg:gap-2 text-sm font-medium
                            absolute lg:static top-20 left-0 right-0 bg-sagara-dark lg:bg-transparent
                            px-4 pb-6 pt-2 lg:p-0 border-b border-white/10 lg:border-0">

                    @include('partials.preferences')

                    @auth
                        @if (auth()->user()->user_type === 'customer')
                            <a href="{{ route('bookings.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.my_bookings') }}</a>
                        @endif

                        @if (auth()->user()->user_type === 'partner')
                            @if (auth()->user()->partner?->hasSignedAgreement())
                                <a href="{{ route('partner.hotels.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.my_hotels') }}</a>
                                <a href="{{ route('partner.bookings.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.incoming_bookings') }}</a>
                            @endif
                            <a href="{{ route('partner.agreement.show') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.mou_menu') }}</a>
                            <a href="{{ route('partner.profile.edit') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.partner_profile') }}</a>
                        @endif

                        @if (auth()->user()->user_type === 'admin')
                            <a href="{{ route('admin.partners.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.verify_partners') }}</a>
                            <a href="{{ route('admin.hotels.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.review_hotels') }}</a>
                            <a href="{{ route('admin.bookings.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.all_bookings') }}</a>
                            <a href="{{ route('admin.companies.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/5 hover:text-sky-300 transition">{{ __('ui.companies') }}</a>
                        @endif

                        @if (auth()->user()->user_type === 'corporate')
                            <a href="{{ route('corporate.dashboard') }}">{{ __('ui.corp_dashboard') }}</a>

                            @if (auth()->user()->companyUser?->isFinance())
                                <a href="{{ route('corporate.employees.index') }}">{{ __('ui.employees') }}</a>
                                <a href="{{ route('corporate.positions.index') }}">{{ __('ui.positions_limits') }}</a>
                            @endif
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="w-full text-left px-4 py-2 rounded-xl border border-white/20 hover:bg-white/10 transition">{{ __('ui.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-slate-200 hover:text-white transition">{{ __('ui.login') }}</a>
                        <a href="{{ route('register.partner') }}" class="px-3 py-2 text-slate-200 hover:text-sky-300 transition">{{ __('ui.register_partner') }}</a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 text-center font-semibold text-sagara-dark bg-gradient-to-r from-sky-300 to-sagara-teal hover:from-sky-200 hover:to-sky-400 rounded-xl shadow-md transition">{{ __('ui.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    @if (session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-2">
            @if (session('success'))
                <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-sagara-dark text-slate-400 text-xs border-t border-slate-800 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div class="max-w-sm space-y-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-sagara-teal flex items-center justify-center text-sagara-dark">
                            <i class="fa-solid fa-water"></i>
                        </div>
                        <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
                    </a>
                    <p class="leading-relaxed">Platform reservasi penginapan & wisata bahari terpadu Nusantara. Transparan, aman, dan berstandar internasional.</p>
                    <div class="flex items-center gap-3 text-slate-300 text-base pt-2">
                        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-sagara-teal hover:text-sagara-dark flex items-center justify-center transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-sagara-teal hover:text-sagara-dark flex items-center justify-center transition"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-sagara-teal hover:text-sagara-dark flex items-center justify-center transition"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-10">
                    <div>
                        <h4 class="text-white font-bold text-sm mb-3">Destinasi</h4>
                        <ul class="space-y-2">
                            <li>Raja Ampat</li><li>Labuan Bajo</li><li>Nusa Penida</li><li>Karimunjawa</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-sm mb-3">Kemitraan</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('register.partner') }}" class="hover:text-sky-300 transition">{{ __('ui.register_partner') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-slate-800 text-slate-500">
                © {{ date('Y') }} {{ config('app.name') }}. Hak Cipta Dilindungi Undang-Undang.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', () => {
            document.getElementById('nav-menu').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>