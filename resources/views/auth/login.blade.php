@extends('layouts.storefront')

@section('content')
    <section class="relative min-h-screen bg-slate-900 text-white flex items-center px-4 sm:px-6 lg:px-8 pt-24 pb-16 overflow-hidden">

        {{-- Background foto (sama gayanya dengan hero beranda) --}}
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1920&q=80"
                 alt="Background Pegunungan"
                 class="w-full h-full object-cover object-center scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-slate-950/80"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/60 via-transparent to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            {{-- KIRI: teks sambutan --}}
            <div class="text-center lg:text-left">
                <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] uppercase tracking-widest font-black px-3.5 py-1.5 rounded-full inline-block mb-4">
                    SekalaGo
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight drop-shadow-md">
                    Welcome<br class="hidden lg:block"> back!
                </h1>
                <p class="mt-4 text-base sm:text-lg text-slate-100 font-medium max-w-md mx-auto lg:mx-0 drop-shadow-sm">
                    Masuk untuk melanjutkan pemesanan hotel & rental mobil terbaik untuk liburan Anda.
                </p>
            </div>

            {{-- KANAN: kartu login --}}
            <div class="w-full max-w-md mx-auto lg:mr-0 lg:ml-auto">
                <div class="bg-white rounded-3xl p-6 sm:p-8 text-slate-800 shadow-2xl border border-slate-100/90 ring-1 ring-slate-900/5">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">{{ __('ui.login') }}</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Masuk ke akun Anda untuk melanjutkan.</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        @error('email')
                            <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                                <i class="fa-solid fa-envelope text-sky-500 mr-1"></i> {{ __('ui.email') }}
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="{{ __('ui.email') }}" required autofocus
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition">
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                                <i class="fa-solid fa-lock text-sky-500 mr-1"></i> {{ __('ui.password') }}
                            </label>
                            <div class="relative">
                                <input id="password" type="password" name="password"
                                       placeholder="{{ __('ui.password') }}" required
                                       class="w-full px-4 py-3 pr-12 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition">
                                <button type="button" id="toggle-password"
                                        class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-sky-600 transition"
                                        aria-label="Tampilkan password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-500 focus:ring-sky-500">
                                {{ __('ui.remember_me') }}
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 hover:underline">
                                    {{ __('ui.forgot_password') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow-lg shadow-sky-500/20 transition duration-300 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i> {{ __('ui.login') }}
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500 space-y-1.5">
                        <p>
                            <a href="{{ route('register') }}" class="font-bold text-sky-600 hover:underline">{{ __('ui.register') }}</a>
                        </p>
                        {{-- <p>
                            <a href="{{ route('register.partner') }}" class="font-bold text-slate-700 hover:text-sky-600 hover:underline">{{ __('ui.register_partner') }}</a>
                        </p> --}}
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('password');
        const btn = document.getElementById('toggle-password');
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show
                ? '<i class="fa-regular fa-eye-slash"></i>'
                : '<i class="fa-regular fa-eye"></i>';
        });
    })();
</script>
@endpush