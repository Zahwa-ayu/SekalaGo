@extends('layouts.app')

@section('content')
    {{-- BANNER GELAP: berada di bawah navbar transparan agar menu putih tetap terbaca --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-sky-900 pt-24 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-300">{{ __('ui.corp_dashboard') }}</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white">
                    {{ $company->company_name }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 pt-2 text-sm font-semibold">
                    <span class="text-white font-bold">{{ $member->displayName() }}</span>
                    @if ($member->position)
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-300">{{ $member->position->name }}</span>
                    @endif
                    <span class="text-slate-500">•</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-sky-200 border border-white/10">{{ __('ui.role_' . $member->role_in_company) }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ route('home') }}" class="px-5 py-3 bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold rounded-xl shadow-md shadow-sky-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.find_hotel') }}
                </a>
                <a href="{{ route('bookings.index') }}" class="px-5 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-bold rounded-xl transition flex items-center gap-2">
                    <i class="fa-solid fa-suitcase"></i> {{ __('ui.my_bookings') }}
                </a>
            </div>
        </div>
    </div>

    {{-- KONTEN: -mt-12 membuat kartu pertama sedikit menimpa banner --}}
    <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-6">

        {{-- LIMIT & BUDGET KARYAWAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-lg p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.your_limit') }}</span>
                </div>
                <div class="text-3xl font-black text-slate-900 mt-1">{{ format_price($limit, 'IDR') }}</div>
                <div class="text-xs text-slate-500">
                    ({{ $member->position ? $member->position->name : __('ui.limit_from_default') }})
                </div>
            </div>

            @if ($budget !== null)
                <div class="border-t sm:border-t-0 sm:border-l border-slate-100 sm:pl-8 pt-4 sm:pt-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.monthly_budget') }}</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ format_price($budget, 'IDR') }}</div>
                    <div class="text-xs text-slate-500 flex flex-wrap gap-2">
                        <span>{{ __('ui.used_this_month') }}: <strong class="text-slate-700">{{ format_price($spent, 'IDR') }}</strong></span>
                        <span>•</span>
                        <span>{{ __('ui.remaining') }}: <strong class="text-emerald-600">{{ format_price(max(0, $budget - $spent), 'IDR') }}</strong></span>
                    </div>
                </div>
            @endif
        </div>

        {{-- KHUSUS FINANCE --}}
        @if ($member->isFinance())

            <div>
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">
                    {{ \Illuminate\Support\Facades\Lang::has('ui.financial_overview') ? __('ui.financial_overview') : 'Financial Overview' }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg mb-3">
                            <i class="fa-solid fa-vault"></i>
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.deposit_balance') }}</div>
                        <div class="text-xl font-black text-slate-900 mt-1">{{ format_price($company->deposit_balance, 'IDR') }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg mb-3">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.default_limit') }}</div>
                        <div class="text-xl font-black text-slate-900 mt-1">{{ format_price($company->travel_policy_limit, 'IDR') }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-lg mb-3">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.employees_count') }}</div>
                        <div class="text-xl font-black text-slate-900 mt-1">{{ $employeeCount }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border {{ $pendingApprovals > 0 ? 'border-amber-200' : 'border-slate-100' }} shadow-md p-5 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl {{ $pendingApprovals > 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-lg mb-3">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.pending_approvals_count') }}</div>
                            <div class="text-xl font-black text-slate-900 mt-1">{{ $pendingApprovals }}</div>
                        </div>
                        @if ($pendingApprovals > 0)
                            <div class="mt-3">
                                <a href="{{ route('corporate.bookings.index') }}" class="text-xs font-bold text-sky-500 hover:text-sky-600 hover:underline flex items-center gap-1">
                                    {{ __('ui.review') }} <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Transaksi Terbaru --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100">
                    <span class="font-extrabold text-slate-900">{{ __('ui.recent_transactions') }}</span>
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
                                <tr class="hover:bg-sky-50/50 transition">
                                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-700 whitespace-nowrap">{{ __('ui.ledger_type_' . $tx->type) }}</td>
                                    <td class="px-6 py-4 font-bold {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-slate-900' }} whitespace-nowrap">
                                        {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $tx->note }}</td>
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