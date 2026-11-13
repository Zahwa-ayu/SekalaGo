@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];
        $schedule = $summary['schedule'] ?? 'monthly';
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.payouts') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.partner_payouts_hint') }}</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_ready') }}</div>
                <div class="text-2xl font-black text-emerald-600">{{ $money($summary['eligible'] ?? 0) }}</div>
                <div class="text-[11px] text-slate-500">{{ __('ui.payout_ready_hint') }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_waiting') }}</div>
                <div class="text-2xl font-black text-slate-700">{{ $money($summary['waiting'] ?? 0) }}</div>
                <div class="text-[11px] text-slate-500">{{ __('ui.payout_waiting_hint') }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_schedule') }}</div>
                <div class="text-2xl font-black text-slate-900">{{ __('ui.schedule_' . $schedule) }}</div>
                @if ($summary && $summary['next_due'])
                    <div class="text-[11px] text-slate-500">{{ __('ui.payout_next_due') }}: {{ $summary['next_due']->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        {{-- REKENING --}}
        <div class="bg-white rounded-2xl border {{ filled($partner->bank_account_no) ? 'border-slate-100' : 'border-red-200' }} shadow-md p-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_bank_account') }}</div>
                @if (filled($partner->bank_account_no))
                    <div class="font-mono text-sm text-slate-900 mt-1 break-all">{{ $partner->bank_account_no }}</div>
                @else
                    <div class="text-sm font-semibold text-red-600 mt-1">{{ __('ui.payout_no_bank_partner') }}</div>
                @endif
            </div>
            <a href="{{ route('partner.profile.edit') }}" class="text-sm font-bold text-sky-600 hover:underline">
                <i class="fa-solid fa-pen mr-1"></i> {{ __('ui.business_profile') }}
            </a>
        </div>

        {{-- RIWAYAT --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.payout_history') }}</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.payout_code') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.payout_amount') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.col_orders') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.account_status') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.payout_reference') }}</th>
                            <th class="text-left px-6 py-2.5">{{ __('ui.payout_paid_on') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $payout)
                            <tr>
                                <td class="px-6 py-2.5 font-mono text-xs text-slate-700">{{ $payout->code }}</td>
                                <td class="px-3 py-2.5 text-right font-black text-slate-900">{{ $money($payout->total_amount) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600">{{ $payout->booking_count }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $statusStyle[$payout->status] }}">{{ __('ui.payout_status_' . $payout->status) }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-600">{{ $payout->transfer_reference ?: '-' }}</td>
                                <td class="px-6 py-2.5 text-xs text-slate-500">{{ $payout->paid_at?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.payout_partner_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">{{ $history->links() }}</div>
        </div>
    </div>
@endsection