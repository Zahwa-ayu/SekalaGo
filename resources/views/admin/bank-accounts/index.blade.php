@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.bank_accounts') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.bank_accounts_hint') }}</p>
        </div>

        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-3 text-xs text-amber-800">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.bank_accounts_warning') }}
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.bank-accounts.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf
            <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-circle-plus text-sky-500 mr-1"></i> {{ __('ui.bank_account_add') }}</h2>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_name') }}</label>
                    <input name="bank_name" list="bank-list" required maxlength="60" value="{{ old('bank_name') }}" class="{{ $input }}">
                    <datalist id="bank-list">@foreach (config('sekalago.banks') as $bank)<option value="{{ $bank }}"></option>@endforeach</datalist>
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_account_no') }}</label>
                    <input name="account_number" required inputmode="numeric" maxlength="40" value="{{ old('account_number') }}" class="{{ $input }} font-mono">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_account_name') }}</label>
                    <input name="account_name" required maxlength="100" value="{{ old('account_name') }}" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.bank_account_note') }}</label>
                <input name="note" maxlength="300" value="{{ old('note') }}" placeholder="{{ __('ui.bank_account_note_hint') }}" class="{{ $input }}">
            </div>

            <div class="flex flex-wrap items-center gap-5">
                <div class="w-28">
                    <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', 0) }}" class="{{ $input }}">
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 mt-5">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-sky-500"> {{ __('ui.account_active') }}
                </label>
            </div>

            <button class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.save_changes') }}</button>
        </form>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="text-left px-6 py-3">{{ __('ui.bank_accounts') }}</th>
                        <th class="text-right px-3 py-3">{{ __('ui.topup_requests_count') }}</th>
                        <th class="text-left px-3 py-3">{{ __('ui.account_status') }}</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900">{{ $account->bank_name }} <span class="font-mono">{{ $account->account_number }}</span></div>
                                <div class="text-xs text-slate-500">a.n. {{ $account->account_name }}@if ($account->note) &middot; {{ $account->note }}@endif</div>
                            </td>
                            <td class="px-3 py-3 text-right text-slate-600">{{ $account->topup_requests_count }}</td>
                            <td class="px-3 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $account->is_active ? __('ui.account_active') : __('ui.account_inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="text-sm font-bold text-sky-600 hover:underline">{{ __('ui.edit') }}</a>
                                    @if ($account->topup_requests_count === 0)
                                        <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" onsubmit="return confirm(@js(__('ui.bank_account_delete_confirm')))">
                                            @csrf @method('DELETE')
                                            <button class="text-sm font-bold text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400">{{ __('ui.bank_accounts_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection