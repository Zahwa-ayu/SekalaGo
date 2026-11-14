@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');

        $stateStyle = [
            'due'           => 'bg-emerald-100 text-emerald-700',
            'not_due'       => 'bg-slate-100 text-slate-600',
            'below_minimum' => 'bg-amber-100 text-amber-700',
            'no_bank'       => 'bg-red-100 text-red-700',
        ];
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];

        $ready    = $rows->filter(fn ($row) => $row['eligible'] > 0);
        $dueCount = $rows->where('state', 'due')->count();

        $cards = [
            ['fa-hand-holding-dollar', 'payout_card_ready', $money($totals['eligible']), 'bg-emerald-100 text-emerald-600'],
            ['fa-hourglass-half', 'payout_card_waiting', $money($totals['waiting']), 'bg-slate-100 text-slate-600'],
            ['fa-paper-plane', 'payout_card_pending', $money($totals['pending']), 'bg-amber-100 text-amber-600'],
            ['fa-circle-check', 'payout_card_paid_month', $money($totals['paid']), 'bg-sky-100 text-sky-600'],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.payouts') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.payouts_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- KARTU --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($cards as [$icon, $label, $value, $tone])
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 leading-tight">{{ __('ui.' . $label) }}</div>
                    </div>
                    <div class="text-lg xl:text-xl font-black text-slate-900 break-words">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- SIAP DICAIRKAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 pt-6 pb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-extrabold text-slate-900">{{ __('ui.payout_ready_title') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('ui.payout_rules_hint', [
                        'hold' => config('sekalago.payout_hold_days', 1),
                        'minimum' => $money(config('sekalago.payout_minimum', 100000)),
                    ]) }}</p>
                </div>

                @if ($dueCount > 0)
                    <form method="POST" action="{{ route('admin.payouts.generate') }}"
                          onsubmit="return confirm(@js(__('ui.payout_generate_confirm', ['count' => $dueCount])))">
                        @csrf
                        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition">
                            <i class="fa-solid fa-bolt mr-1"></i> {{ __('ui.payout_generate', ['count' => $dueCount]) }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.col_partner') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.payout_schedule') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.payout_ready') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.payout_waiting') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.payout_next_due') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.account_status') }}</th>
                            <th class="px-6 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ready as $row)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-900">{{ $row['partner']->company_name }}</div>
                                    <div class="text-[11px] text-slate-500 break-all">{{ $row['partner']->bank_account_no ?: __('ui.payout_no_bank_short') }}</div>
                                </td>
                                <td class="px-3 py-3 text-slate-600 text-xs">{{ __('ui.schedule_' . $row['schedule']) }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="font-black text-slate-900">{{ $money($row['eligible']) }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $row['count'] }} {{ __('ui.col_orders') }}</div>
                                </td>
                                <td class="px-3 py-3 text-right text-slate-500">{{ $money($row['waiting']) }}</td>
                                <td class="px-3 py-3 text-xs text-slate-600">
                                    {{ $row['next_due']?->format('d/m/Y') ?? '-' }}
                                    @if ($row['last_at'])
                                        <div class="text-[11px] text-slate-400">{{ __('ui.payout_last', ['date' => $row['last_at']->format('d/m/Y')]) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $stateStyle[$row['state']] }}">{{ __('ui.payout_state_' . $row['state']) }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if ($row['state'] !== 'no_bank')
                                        <form method="POST" action="{{ route('admin.payouts.store') }}"
                                              onsubmit="return confirm(@js(__('ui.payout_create_confirm', ['partner' => $row['partner']->company_name, 'amount' => $money($row['eligible'])])))">
                                            @csrf
                                            <input type="hidden" name="partner_id" value="{{ $row['partner']->id }}">
                                            <button class="px-3.5 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold transition whitespace-nowrap">
                                                {{ __('ui.payout_create') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.payout_ready_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIWAYAT --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 pt-6 pb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-extrabold text-slate-900">{{ __('ui.payout_history') }}</h2>

                <div class="flex flex-wrap gap-2">
                    @foreach ([null => 'all', 'pending' => 'payout_status_pending', 'paid' => 'payout_status_paid', 'cancelled' => 'payout_status_cancelled'] as $value => $label)
                        <a href="{{ route('admin.payouts.index', $value ? ['status' => $value] : []) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ (string) $status === (string) $value ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ __('ui.' . $label) }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.payout_code') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.col_partner') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.payout_amount') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.col_orders') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.account_status') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.payout_created_on') }}</th>
                            <th class="px-6 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $payout)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-3 font-mono text-xs text-slate-700">{{ $payout->code }}</td>
                                <td class="px-3 py-3 font-semibold text-slate-800">{{ $payout->partner->company_name }}</td>
                                <td class="px-3 py-3 text-right font-black text-slate-900">{{ $money($payout->total_amount) }}</td>
                                <td class="px-3 py-3 text-right text-slate-600">{{ $payout->booking_count }}</td>
                                <td class="px-3 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $statusStyle[$payout->status] }}">{{ __('ui.payout_status_' . $payout->status) }}</span>
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ $payout->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 text-right">
                                    {{-- <a href="{{ route('admin.payouts.show', $payout) }}" class="text-sm font-bold text-sky-600 hover:underline">{{ __('ui.view') }}</a>
                                </td> --}}
                                <a href="{{ route('admin.payouts.show', $payout) }}"
                                    class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition whitespace-nowrap">
                                    {{ __('ui.view') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.no_results') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">{{ $history->links() }}</div>
        </div>
    </section>
@endsection