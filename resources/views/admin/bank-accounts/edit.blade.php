@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md';
        $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.bank-accounts.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.bank_accounts') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $account->bank_name }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM KIRI: FORM --}}
            <form method="POST" action="{{ route('admin.bank-accounts.update', $account) }}" class="lg:col-span-2 {{ $card }} p-6 space-y-5">
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
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.bank_account_note_hint') }}</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                        <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $account->sort_order) }}" class="{{ $input }}">
                    </div>
                    <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 pb-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active)) class="rounded border-slate-300 text-sagara-blue">
                        {{ __('ui.account_active') }}
                    </label>
                </div>

                <div class="flex flex-wrap gap-3 pt-5 border-t border-slate-100">
                    <button class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-check"></i> {{ __('ui.save_changes') }}
                    </button>
                    <a href="{{ route('admin.bank-accounts.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.cancel') }}</a>
                </div>
            </form>

            {{-- KOLOM KANAN: KETERANGAN --}}
            <div class="{{ $card }} p-6 space-y-3 text-xs text-slate-600 lg:sticky lg:top-24">
                <div class="font-extrabold text-slate-900 text-sm"><i class="fa-solid fa-circle-info text-sagara-blue"></i> {{ __('ui.bank_accounts') }}</div>
                <p>{{ __('ui.bank_accounts_hint') }}</p>
                <p class="px-3 py-2 rounded-xl bg-amber-50 border border-amber-100 text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.bank_accounts_warning') }}
                </p>
            </div>
        </div>
    </section>
@endsection