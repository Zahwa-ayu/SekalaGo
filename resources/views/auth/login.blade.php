@extends('layouts.app')

@section('content')
    <section class="hero-bg min-h-[calc(100vh-5rem)] px-4 py-12 flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center text-white mb-6">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg mb-4">
                    <i class="fa-solid fa-water text-sagara-dark text-2xl"></i>
                </div>
                <h1 class="text-3xl font-black tracking-tight">{{ __('ui.login') }}</h1>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/60">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    @error('email')
                        <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-envelope text-sagara-blue"></i> {{ __('ui.email') }}
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required
                               class="w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none">
                    </div>

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-lock text-sagara-blue"></i> {{ __('ui.password') }}
                        </label>
                        <input type="password" name="password" placeholder="{{ __('ui.password') }}" required
                               class="w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none">
                    </div>

                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded text-sagara-blue focus:ring-sagara-blue">
                        {{ __('ui.remember_me') }}
                    </label>

                    <button class="w-full py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> {{ __('ui.login') }}
                    </button>
                </form>

                <p class="text-center text-xs text-slate-500 mt-6">
                    <a href="{{ route('register') }}" class="font-bold text-sagara-blue hover:underline">{{ __('ui.register') }}</a>
                    &nbsp;•&nbsp;
                    <a href="{{ route('register.partner') }}" class="font-bold text-sagara-blue hover:underline">{{ __('ui.register_partner') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection