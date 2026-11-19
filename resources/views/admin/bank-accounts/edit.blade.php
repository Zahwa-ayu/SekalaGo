@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('admin.bank-accounts.index') }}" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.bank_accounts') }}</a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $account->bank_name }}</h1>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.bank-accounts.update', $account) }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_name') }}</label>
                    <input name="bank_name" list="bank-list" required maxlength="60" value="{{ old('bank_name', $account->bank_name) }}" class="{{ $input }}">
                    <datalist id="bank-list">@foreach (config('sekalago.banks') as $bank)<option value="{{ $bank }}"></option>@endforeach</datalist>
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_account_no') }}</label>
                    <input name="account_number" required inputmode="numeric" maxlength="40" value="{{ old('account_number', $account->account_number) }}" class="{{ $input }} font-mono">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.bank_account_name') }}</label>
                    <input name="account_name" required maxlength="100" value="{{ old('account_name', $account->account_name) }}" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.bank_account_note') }}</label>
                <input name="note" maxlength="300" value="{{ old('note', $account->note) }}" class="{{ $input }}">
            </div>

            <div class="flex flex-wrap items-center gap-5">
                <div class="w-28">
                    <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $account->sort_order) }}" class="{{ $input }}">
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 mt-5">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active)) class="rounded border-slate-300 text-sky-500"> {{ __('ui.account_active') }}
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.save_changes') }}</button>
                <a href="{{ route('admin.bank-accounts.index') }}" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </section>
@endsection