@extends('layouts.app')

@section('content')
    <section class="hero-bg min-h-[calc(100vh-5rem)] px-4 py-12 flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center text-white mb-6">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-sagara-teal to-sky-400 flex items-center justify-center shadow-lg mb-4">
                    <i class="fa-solid fa-user-plus text-sagara-dark text-2xl"></i>
                </div>
                <h1 class="text-3xl font-black tracking-tight">{{ __('ui.register') }}</h1>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/60">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    @if ($errors->any())
                        <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @php
                        $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
                        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
                        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
                    @endphp

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-user text-sagara-blue"></i> {{ __('ui.full_name') }}</label>
                        <input name="full_name" value="{{ old('full_name') }}" placeholder="{{ __('ui.full_name') }}" required class="{{ $input }}">
                    </div>

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sagara-blue"></i> {{ __('ui.email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required class="{{ $input }}">
                    </div>

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-phone text-sagara-blue"></i> {{ __('ui.phone') }}</label>
                        <input name="phone_number" value="{{ old('phone_number') }}" placeholder="{{ __('ui.phone') }}" class="{{ $input }}">
                    </div>

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-lock text-sagara-blue"></i> {{ __('ui.password') }}</label>
                        <input type="password" name="password" placeholder="{{ __('ui.password') }}" required class="{{ $input }}">
                    </div>

                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-lock text-sagara-blue"></i> {{ __('ui.password_confirm') }}</label>
                        <input type="password" name="password_confirmation" placeholder="{{ __('ui.password_confirm') }}" required class="{{ $input }}">
                    </div>

                    <button class="w-full py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> {{ __('ui.register') }}
                    </button>
                </form>

                <p class="text-center text-xs text-slate-500 mt-6">
                    <a href="{{ route('login') }}" class="font-bold text-sagara-blue hover:underline">{{ __('ui.login') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection