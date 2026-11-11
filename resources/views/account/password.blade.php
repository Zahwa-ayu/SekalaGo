@extends('account.layout')

@section('account')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    @if (auth()->user()->must_change_password)
        <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.must_change_password_notice') }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}"
          class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-4 max-w-xl">
        @csrf
        @method('PUT')

        <div>
            <label class="{{ $label }}">{{ __('ui.current_password') }}</label>
            <input type="password" name="current_password" required autofocus class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.new_password') }}</label>
            <input type="password" name="password" required class="{{ $input }}">
            <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.password_rules_hint') }}</p>
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.confirm_password') }}</label>
            <input type="password" name="password_confirmation" required class="{{ $input }}">
        </div>

        <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
            {{ __('ui.save_password') }}
        </button>
    </form>
@endsection