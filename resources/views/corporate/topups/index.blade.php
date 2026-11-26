@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'approved'  => 'bg-emerald-100 text-emerald-700',
            'rejected'  => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.topup_balance') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('ui.topup_balance_hint') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-3">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.deposit_balance') }}</div>
                <div class="text-xl font-black text-slate-900">{{ $money($company->deposit_balance) }}</div>
            </div>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- 1. REKENING TUJUAN --}}
        <div class="space-y-3">
            <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">1.</span> {{ __('ui.topup_step_transfer') }}</h2>

            @forelse ($accounts as $account)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $account->bank_name }}</div>
                        <div class="font-mono text-xl font-black text-slate-900 break-all">{{ $account->account_number }}</div>
                        <div class="text-sm text-slate-600">a.n. {{ $account->account_name }}</div>
                        @if ($account->note)<div class="text-xs text-slate-500 mt-1">{{ $account->note }}</div>@endif
                    </div>
                    <button type="button"
                            onclick="navigator.clipboard.writeText(@js(preg_replace('/\D/', '', $account->account_number))).then(() => { this.textContent = @js(__('ui.copied')) })"
                            class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:bg-sky-50 hover:text-sky-600 transition">
                        {{ __('ui.copy_number') }}
                    </button>
                </div>
            @empty
                <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.topup_no_accounts') }}
                </div>
            @endforelse

            <div class="rounded-2xl bg-sky-50 border border-sky-100 px-5 py-3 text-xs text-sky-900">
                <i class="fa-solid fa-circle-info"></i>
                {{ __('ui.topup_transfer_memo', ['code' => $company->corporate_code, 'minimum' => $money($minimum)]) }}
            </div>
        </div>

        {{-- 2. PENGAJUAN --}}
        @if ($accounts->isNotEmpty())
            <form method="POST" action="{{ route('corporate.topups.store') }}" enctype="multipart/form-data"
                  class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-4">
                @csrf
                <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">2.</span> {{ __('ui.topup_step_submit') }}</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.topup_to_account') }}</label>
                        <select name="platform_bank_account_id" required class="{{ $input }}">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((int) old('platform_bank_account_id') === $account->id)>{{ $account->bank_name }} {{ $account->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.topup_amount') }}</label>
                        <input type="number" name="amount" required min="{{ $minimum }}" max="1000000000" value="{{ old('amount') }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.payout_paid_on') }}</label>
                        <input type="date" name="transfer_date" required max="{{ today()->toDateString() }}" min="{{ today()->subDays(30)->toDateString() }}"
                               value="{{ old('transfer_date', today()->toDateString()) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.topup_sender') }}</label>
                        <input name="sender_name" maxlength="100" value="{{ old('sender_name') }}" placeholder="{{ __('ui.topup_sender_hint') }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">{{ __('ui.payout_proof') }}</label>
                        <input type="file" name="proof" required accept="image/jpeg,image/png,image/webp,application/pdf"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:file:bg-emerald-100">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.payout_proof_hint') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">{{ __('ui.audit_col_note') }}</label>
                        <input name="note" maxlength="300" value="{{ old('note') }}" class="{{ $input }}">
                    </div>
                </div>

                <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.topup_submit') }}</button>
                <p class="text-[11px] text-slate-500">{{ __('ui.topup_review_note') }}</p>
            </form>
        @endif

        {{-- FILTER TANGGAL --}}
        <x-date-filter />

        {{-- RIWAYAT --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.topup_history') }}</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.payout_code') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.payout_amount') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.account_status') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.payout_created_on') }}</th>
                            <th class="px-6 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $topup)
                            <tr class="align-top">
                                <td class="px-6 py-3">
                                    <div class="font-mono text-xs font-bold text-slate-800">{{ $topup->code }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $topup->account?->bank_name }} &middot; {{ $topup->transfer_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <div class="font-black text-slate-900">{{ $money($topup->amount) }}</div>
                                    @if ($topup->status === 'approved' && (int) $topup->approved_amount !== (int) $topup->amount)
                                        <div class="text-[11px] font-bold text-emerald-600">{{ __('ui.topup_credited', ['amount' => $money($topup->approved_amount)]) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $statusStyle[$topup->status] }}">{{ __('ui.topup_status_' . $topup->status) }}</span>
                                    @if ($topup->decision_note)<div class="mt-1 text-[11px] text-slate-500 max-w-56">{{ $topup->decision_note }}</div>@endif
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $topup->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex justify-end items-center gap-3 text-xs font-bold">
                                        <a href="{{ route('corporate.topups.proof', $topup) }}" target="_blank" rel="noopener" class="text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.payout_proof') }}</a>
                                        @if ($topup->status === 'pending')
                                            <form method="POST" action="{{ route('corporate.topups.cancel', $topup) }}" onsubmit="return confirm(@js(__('ui.topup_cancel_confirm')))">
                                                @csrf
                                                <button class="text-red-600 hover:underline">{{ __('ui.cancel') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.topup_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">{{ $requests->links() }}</div>
        </div>
    </section>
@endsection