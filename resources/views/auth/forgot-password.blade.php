@extends('layouts.app')

@section('content')
    <section class="min-h-[70vh] flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-xl p-8 space-y-5">
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-100 text-sagara-blue flex items-center justify-center text-2xl mb-3">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.forgot_password') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('ui.forgot_password_hint') }}</p>
            </div>

            @if ($errors->any())
                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif

            @if (session('demo_link'))
                <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 space-y-1">
                    <p class="font-bold"><i class="fa-solid fa-flask"></i> {{ __('ui.reset_demo_notice') }}</p>
                    <a href="{{ session('demo_link') }}" class="underline break-all">{{ session('demo_link') }}</a>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Email"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">

                <button class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white text-sm font-bold shadow-lg transition">
                    {{ __('ui.send_reset_link') }}
                </button>
            </form>

            <a href="{{ route('login') }}" class="block text-center text-sm font-bold text-sagara-blue hover:underline">
                <i class="fa-solid fa-arrow-left"></i> {{ __('ui.back_to_login') }}
            </a>
        </div>
    </section>
@endsection