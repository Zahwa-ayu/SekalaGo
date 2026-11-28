@extends('account.layout')

@section('account')
    @php
        $fromList = \Illuminate\Support\Str::before(url()->previous(), '?') === url('/notifications');
        $backUrl  = $fromList ? url()->previous() : route('account.settings');
    @endphp

    <div>
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sky-600 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('account.notifications.update') }}"
          class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-5">
        @csrf
        @method('PUT')

        <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.notification_settings') }}</h2>

        <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-700">
            <i class="fa-regular fa-bell text-sky-500"></i> {{ __('ui.notification_inapp_note') }}
        </div>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="notify_email" value="1" @checked($user->notify_email) class="mt-1 rounded border-slate-300 text-sky-500">
            <span>
                <span class="block text-sm font-bold text-slate-900">{{ __('ui.notification_email_label') }}</span>
                <span class="block text-xs text-slate-500">{{ __('ui.notification_email_hint', ['email' => $user->email]) }}</span>
            </span>
        </label>

        <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
            {{ __('ui.save_changes') }}
        </button>
    </form>
@endsection