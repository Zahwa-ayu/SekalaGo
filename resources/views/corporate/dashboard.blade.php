@extends('layouts.app')

@section('content')
    <div class="bg-slate-50 text-slate-800 min-h-screen font-sans antialiased">

        {{-- 1. HEADER: warna solid (tanpa gambar) supaya navbar transparan tetap terbaca --}}
        <section class="bg-slate-950 text-white pt-24 pb-20 border-b border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 text-[11px] font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-building text-sky-400"></i> {{ __('ui.corp_dashboard') }}
                </span>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    {{ $company->company_name }}
                </h1>

                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                    <span class="text-white font-bold">{{ $member->displayName() }}</span>
                    @if ($member->position)
                        <span class="text-slate-600">•</span>
                        <span class="text-slate-300">{{ $member->position->name }}</span>
                    @endif
                    <span class="text-slate-600">•</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-500/15 text-sky-300 border border-sky-400/30">
                        {{ __('ui.role_' . $member->role_in_company) }}
                    </span>
                </div>

                <div class="pt-1 flex flex-wrap gap-3 items-center">
                    <a href="{{ route('home') }}" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold rounded-xl shadow-md shadow-sky-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.find_hotel') }}
                    </a>
                    <a href="{{ route('bookings.index') }}" class="px-5 py-2.5 bg-slate-900/80 hover:bg-slate-800 text-slate-200 text-sm font-bold rounded-xl border border-slate-700/80 transition flex items-center gap-2">
                        <i class="fa-solid fa-suitcase text-sky-400"></i> {{ __('ui.my_bookings') }}
                    </a>
                </div>
            </div>
        </section>

        {{-- KONTEN: -mt-10 membuat kartu pertama sedikit menimpa header --}}
        <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 pb-10 space-y-6">

            {{-- 2. LIMIT & BUDGET KARYAWAN --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-lg p-5">
                <div class="grid grid-cols-1 {{ $budget !== null ? 'md:grid-cols-2' : '' }} gap-5 items-center">

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-base">
                            <i class="fa-solid fa-gauge-high"></i>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('ui.your_limit') }}</span>
                            <div class="text-2xl font-black text-slate-900 leading-tight">{{ format_price($limit, 'IDR') }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $member->position ? $member->position->name : __('ui.limit_from_default') }}
                            </div>
                        </div>
                    </div>

                    @if ($budget !== null)
                        @php
                            $pct = $budget > 0 ? min(100, (int) round($spent / $budget * 100)) : 0;
                            $bar = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500');
                        @endphp
                        <div class="md:border-l md:border-slate-100 md:pl-5 flex items-start gap-3">
                            <div class="w-10 h-10 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-base">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('ui.monthly_budget') }}</span>
                                <div class="text-2xl font-black text-slate-900 leading-tight">{{ format_price($budget, 'IDR') }}</div>

                                <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                                </div>

                                <div class="text-xs text-slate-500 mt-1.5 flex flex-wrap gap-x-3 gap-y-0.5">
                                    <span>{{ __('ui.used_this_month') }}: <strong class="text-slate-700">{{ format_price($spent, 'IDR') }}</strong></span>
                                    <span>{{ __('ui.remaining') }}: <strong class="text-emerald-600">{{ format_price(max(0, $budget - $spent), 'IDR') }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- 3. KHUSUS FINANCE --}}
            @if ($member->isFinance())

                <div>
                    <h2 class="text-[11px] font-black text-sky-600 uppercase tracking-widest mb-3">
                        {{ \Illuminate\Support\Facades\Lang::has('ui.financial_overview') ? __('ui.financial_overview') : 'Financial Overview' }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                        <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-400 p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-vault"></i>
                            </div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.deposit_balance') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-0.5">{{ format_price($company->deposit_balance, 'IDR') }}</div>
                        </div>

                        <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-400 p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.default_limit') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-0.5">{{ format_price($company->travel_policy_limit, 'IDR') }}</div>
                        </div>

                        <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-400 p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-3 border border-sky-100 transition duration-300">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.employees_count') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-0.5">{{ $employeeCount }}</div>
                        </div>

                        <div class="rounded-2xl bg-white border {{ $pendingApprovals > 0 ? 'border-amber-300' : 'border-slate-200/80 hover:border-sky-400' }} p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-xl {{ $pendingApprovals > 0 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-100 text-slate-400 border-slate-200' }} flex items-center justify-center mb-3 border">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.pending_approvals_count') }}</div>
                                <div class="text-xl font-black text-slate-900 mt-0.5">{{ $pendingApprovals }}</div>
                            </div>
                            @if ($pendingApprovals > 0)
                                <a href="{{ route('corporate.bookings.index') }}" class="mt-2 text-xs font-extrabold text-sky-600 hover:text-sky-700 hover:underline flex items-center gap-1">
                                    {{ __('ui.review') }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- 4. TRANSAKSI TERBARU --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-md overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-sky-600"></i>
                        <span class="font-extrabold text-slate-900">{{ __('ui.recent_transactions') }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-5 py-3 font-bold">{{ __('ui.date') }}</th>
                                    <th class="px-5 py-3 font-bold">{{ __('ui.status') }}</th>
                                    <th class="px-5 py-3 font-bold">{{ __('ui.amount') }}</th>
                                    <th class="px-5 py-3 font-bold">{{ __('ui.balance_after') }}</th>
                                    <th class="px-5 py-3 font-bold">{{ __('ui.note') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($transactions as $tx)
                                    <tr class="hover:bg-sky-50/50 transition">
                                        <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ __('ui.ledger_type_' . $tx->type) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 font-bold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                                            {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                        </td>
                                        <td class="px-5 py-3 font-bold text-slate-900 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                        <td class="px-5 py-3 text-xs text-slate-500">{{ $tx->note }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                            <i class="fa-solid fa-receipt text-2xl mb-2 block"></i>
                                            <span class="text-xs">{{ __('ui.no_transactions') }}</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif
        </section>
    </div>
@endsection