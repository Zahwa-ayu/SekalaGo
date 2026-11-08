@extends('layouts.app')

@section('content')
    <section class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.change_password') }}</h1>

        @if (auth()->user()->must_change_password)
            <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.must_change_password_notice') }}
            </div>
        @endif

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('account.password.update') }}"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.current_password') }}</label>
                <input type="password" name="current_password" required autofocus
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.new_password') }}</label>
                <input type="password" name="password" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.password_rules_hint') }}</p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.confirm_password') }}</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
            </div>

            <button class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white text-sm font-bold shadow-lg transition">
                {{ __('ui.save_password') }}
            </button>
        </form>
    </section>
@endsection