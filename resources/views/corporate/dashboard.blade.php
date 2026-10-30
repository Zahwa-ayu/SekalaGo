@extends('layouts.app')

@section('content')
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <span class="text-[11px] font-bold text-sky-200 uppercase tracking-wider">{{ __('ui.corp_dashboard') }}</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">{{ $company->company_name }}</h1>

            <p class="text-sm text-sky-100 mt-3 flex flex-wrap items-center gap-x-2 gap-y-1">
                <i class="fa-solid fa-circle-user text-sagara-teal"></i>
                {{ $member->displayName() }}
                @if ($member->position) ({{ $member->position->name }}) @endif
                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-white/15">{{ __('ui.role_' . $member->role_in_company) }}</span>
            </p>

            <div class="mt-5 pt-5 border-t border-white/15">
                <div class="text-[11px] font-bold text-sky-200 uppercase tracking-wider">{{ __('ui.your_limit') }}</div>
                <div class="text-3xl font-black mt-1">{{ format_price($limit, 'IDR') }}</div>
                <div class="text-xs text-sky-200 mt-1">({{ $member->position ? $member->position->name : __('ui.limit_from_default') }})</div>
            </div>
        </div>

        @if ($member->isFinance())
            {{-- STATISTIK --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['fa-wallet', __('ui.deposit_balance'), format_price($company->deposit_balance, 'IDR'), true],
                    ['fa-shield-halved', __('ui.default_limit'), format_price($company->travel_policy_limit, 'IDR'), false],
                    ['fa-users', __('ui.employees_count'), $employeeCount, false],
                ] as [$icon, $lbl, $value, $highlight])
                    <div class="{{ $highlight ? 'bg-gradient-to-br from-sagara-navy to-sagara-blue text-white shadow-xl' : 'bg-white border border-slate-100 shadow-md' }} rounded-2xl p-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-3 {{ $highlight ? 'bg-white/15 text-sagara-teal' : 'bg-sky-100 text-sagara-blue' }}">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div class="text-[11px] font-bold uppercase tracking-wider {{ $highlight ? 'text-sky-200' : 'text-slate-400' }}">{{ $lbl }}</div>
                        <div class="text-xl sm:text-2xl font-black mt-1 {{ $highlight ? '' : 'text-slate-900' }}">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            {{-- TRANSAKSI --}}
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.recent_transactions') }}</h2>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-6 py-4 font-bold">{{ __('ui.date') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('ui.amount') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('ui.balance_after') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($transactions as $tx)
                                    <tr class="hover:bg-sky-50/50 transition">
                                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 font-semibold text-slate-800">{{ __('ui.ledger_type_' . $tx->type) }}</td>
                                        <td class="px-6 py-4 font-bold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                            <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                            {{ __('ui.no_transactions') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection