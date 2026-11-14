@extends('layouts.app')

@section('content')
    @php
        $th   = 'px-6 py-4 font-bold';
        $td   = 'px-6 py-4';
        $chip = 'px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap';

        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];
        $schedule = $summary['schedule'] ?? 'monthly';
    @endphp

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.payouts') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('ui.partner_payouts_hint') }}</p>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- RINGKASAN --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-5 space-y-1">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_ready') }}</div>
            <div class="text-2xl font-black text-emerald-600">{{ $money($summary['eligible'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-500">{{ __('ui.payout_ready_hint') }}</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-5 space-y-1">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_waiting') }}</div>
            <div class="text-2xl font-black text-slate-700">{{ $money($summary['waiting'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-500">{{ __('ui.payout_waiting_hint') }}</div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-5 space-y-1">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_schedule') }}</div>
            <div class="text-2xl font-black text-slate-900">{{ __('ui.schedule_' . $schedule) }}</div>
            @if ($summary && $summary['next_due'])
                <div class="text-[11px] text-slate-500">{{ __('ui.payout_next_due') }}: {{ $summary['next_due']->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    {{-- REKENING --}}
    <div class="bg-white rounded-3xl border {{ filled($partner->bank_account_no) ? 'border-slate-100' : 'border-red-200' }} shadow-md p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_bank_account') }}</div>
            @if (filled($partner->bank_account_no))
                <div class="font-mono text-sm text-slate-900 mt-1 break-all">{{ $partner->bank_account_no }}</div>
            @else
                <div class="text-sm font-semibold text-red-600 mt-1">{{ __('ui.payout_no_bank_partner') }}</div>
            @endif
        </div>
        <a href="{{ route('partner.profile.edit') }}"
           class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-pen mr-1"></i> {{ __('ui.business_profile') }}
        </a>
    </div>

    {{-- RIWAYAT --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.payout_history') }}</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="{{ $th }}">{{ __('ui.payout_code') }}</th>
                        <th class="{{ $th }} text-right">{{ __('ui.payout_amount') }}</th>
                        <th class="{{ $th }} text-right">{{ __('ui.col_orders') }}</th>
                        <th class="{{ $th }}">{{ __('ui.account_status') }}</th>
                        <th class="{{ $th }}">{{ __('ui.payout_reference') }}</th>
                        <th class="{{ $th }}">{{ __('ui.payout_paid_on') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($history as $payout)
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="{{ $td }} font-mono text-xs text-slate-700 whitespace-nowrap">{{ $payout->code }}</td>
                            <td class="{{ $td }} text-right font-black text-slate-900 whitespace-nowrap">{{ $money($payout->total_amount) }}</td>
                            <td class="{{ $td }} text-right text-slate-600">{{ $payout->booking_count }}</td>
                            <td class="{{ $td }}">
                                <span class="{{ $chip }} {{ $statusStyle[$payout->status] }}">
                                    {{ __('ui.payout_status_' . $payout->status) }}
                                </span>
                            </td>
                            <td class="{{ $td }} text-xs text-slate-600">{{ $payout->transfer_reference ?: '-' }}</td>
                            <td class="{{ $td }} text-xs text-slate-500 whitespace-nowrap">{{ $payout->paid_at?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-money-bill-transfer text-3xl mb-2 block"></i>
                                {{ __('ui.payout_partner_empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($history->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $history->links() }}</div>
        @endif
    </div>
</section>
@endsection