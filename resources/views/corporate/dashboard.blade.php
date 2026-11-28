@extends('layouts.app')

@section('content')
    @php
        $isFinance = $member->isFinance();

        $card    = 'bg-white rounded-3xl border border-slate-100 shadow-md';
        $label   = 'text-[11px] font-bold uppercase tracking-wider text-slate-400';
        $iconBox = 'w-10 h-10 shrink-0 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center';
    @endphp

    {{-- BANNER BIRU: khusus karyawan --}}
    @if (! $isFinance)
        <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0 space-y-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-sky-100">
                        <i class="fa-solid fa-building mr-1"></i> {{ __('ui.corp_dashboard') }}
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm break-words">{{ $company->company_name }}</h1>

                    <div class="flex flex-wrap items-center gap-2 text-sm text-sky-100">
                        <span class="font-bold text-white">{{ $member->displayName() }}</span>
                        @if ($member->position)
                            <span>&middot;</span>
                            <span>{{ $member->position->name }}</span>
                        @endif
                        <span class="px-2.5 py-1 rounded-full bg-white text-sky-700 text-[11px] font-bold">{{ __('ui.role_' . $member->role_in_company) }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-sky-700 text-sm font-bold shadow-lg hover:bg-sky-50 transition">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.find_hotel') }}
                    </a>
                    <a href="{{ route('bookings.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/60 text-white text-sm font-bold hover:bg-white/10 transition">
                        <i class="fa-solid fa-suitcase"></i> {{ __('ui.my_bookings') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <section class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 {{ $isFinance ? 'py-8' : '-mt-12 pb-12' }}">

        {{-- HEADER POLOS: khusus Finance --}}
        @if ($isFinance)
            <div class="space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-sky-600">
                    <i class="fa-solid fa-building mr-1"></i> {{ __('ui.corp_dashboard') }}
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words">{{ $company->company_name }}</h1>

                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span class="font-bold text-slate-900">{{ $member->displayName() }}</span>
                    @if ($member->position)
                        <span>&middot;</span>
                        <span>{{ $member->position->name }}</span>
                    @endif
                    <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 text-[11px] font-bold">{{ __('ui.role_' . $member->role_in_company) }}</span>
                </div>
            </div>
        @endif

        {{-- LIMIT DAN BUDGET KARYAWAN --}}
        <div class="{{ $card }} p-6 sm:p-8">
            <div class="grid grid-cols-1 {{ $budget !== null ? 'md:grid-cols-2' : '' }} gap-6 items-start">

                <div class="flex items-start gap-3">
                    <div class="{{ $iconBox }}"><i class="fa-solid fa-gauge-high"></i></div>
                    <div class="min-w-0">
                        <div class="{{ $label }}">{{ __('ui.your_limit') }}</div>
                        <div class="text-2xl font-black text-slate-900">{{ format_price($limit, 'IDR') }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $member->position ? $member->position->name : __('ui.limit_from_default') }}</div>
                    </div>
                </div>

                @if ($budget !== null)
                    @php
                        $pct = $budget > 0 ? min(100, (int) round($spent / $budget * 100)) : 0;
                        $bar = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-sky-500');
                    @endphp

                    <div class="flex items-start gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-wallet"></i></div>
                        <div class="min-w-0 flex-1">
                            <div class="{{ $label }}">{{ __('ui.monthly_budget') }}</div>
                            <div class="text-2xl font-black text-slate-900">{{ format_price($budget, 'IDR') }}</div>

                            <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                            </div>

                            <div class="text-xs text-slate-500 mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                <span>{{ __('ui.used_this_month') }}: <strong class="text-slate-900">{{ format_price($spent, 'IDR') }}</strong></span>
                                <span>{{ __('ui.remaining') }}: <strong class="text-slate-900">{{ format_price(max(0, $budget - $spent), 'IDR') }}</strong></span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- KHUSUS FINANCE --}}
        @if ($isFinance)

            {{-- RINGKASAN KEUANGAN --}}
            <div class="space-y-3">
                <h2 class="text-xl font-extrabold text-slate-900">
                    {{ \Illuminate\Support\Facades\Lang::has('ui.financial_overview') ? __('ui.financial_overview') : 'Financial Overview' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- SALDO DEPOSIT --}}
                    <div class="{{ $card }} p-5 flex flex-col gap-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-vault"></i></div>
                        <div>
                            <div class="{{ $label }}">{{ __('ui.deposit_balance') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-1 break-words">{{ format_price($company->deposit_balance, 'IDR') }}</div>
                        </div>
                        <a href="{{ route('corporate.topups.index') }}"
                           class="mt-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                            <i class="fa-solid fa-wallet"></i> {{ __('ui.topup_balance') }}
                        </a>
                    </div>

                    {{-- LIMIT BAWAAN --}}
                    <div class="{{ $card }} p-5 space-y-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-sliders"></i></div>
                        <div>
                            <div class="{{ $label }}">{{ __('ui.default_limit') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-1 break-words">{{ format_price($company->travel_policy_limit, 'IDR') }}</div>
                        </div>
                    </div>

                    {{-- JUMLAH KARYAWAN --}}
                    <div class="{{ $card }} p-5 space-y-3">
                        <div class="{{ $iconBox }}"><i class="fa-solid fa-users"></i></div>
                        <div>
                            <div class="{{ $label }}">{{ __('ui.employees_count') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-1">{{ $employeeCount }}</div>
                        </div>
                    </div>

                    {{-- MENUNGGU PERSETUJUAN --}}
                    <div class="bg-white rounded-3xl border {{ $pendingApprovals > 0 ? 'border-amber-300' : 'border-slate-100' }} shadow-md p-5 flex flex-col gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $pendingApprovals > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="{{ $label }}">{{ __('ui.pending_approvals_count') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-1">{{ $pendingApprovals }}</div>
                        </div>
                        @if ($pendingApprovals > 0)
                            <a href="{{ route('corporate.bookings.index') }}" class="mt-auto inline-flex items-center gap-2 text-sm font-bold text-sky-600 hover:underline">
                                {{ __('ui.review') }} <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TRANSAKSI TERBARU --}}
            <div class="{{ $card }} overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-receipt text-sky-600 mr-1"></i> {{ __('ui.recent_transactions') }}</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-4 font-bold">{{ __('ui.date') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.amount') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.balance_after') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.note') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($transactions as $tx)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">{{ __('ui.ledger_type_' . $tx->type) }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                                        {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $tx->note }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                        {{ __('ui.no_transactions') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection