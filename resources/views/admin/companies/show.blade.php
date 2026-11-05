@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <div>
            <a href="{{ route('admin.companies.index') }}"
               class="text-xs font-bold text-sagara-blue hover:underline">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.companies') }}
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900">
                {{ $company->company_name }}
                @if ($company->corporate_code)
                    <span class="ml-2 align-middle px-2.5 py-1 text-[11px] font-mono font-bold rounded-full bg-slate-100 text-slate-500">{{ $company->corporate_code }}</span>
                @endif
            </h1>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- RINGKASAN + TOP UP --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <p class="text-[11px] uppercase tracking-wider font-bold text-slate-400">{{ __('ui.deposit_balance') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ format_price($company->deposit_balance, 'IDR') }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <p class="text-[11px] uppercase tracking-wider font-bold text-slate-400">{{ __('ui.default_limit') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ format_price($company->travel_policy_limit, 'IDR') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.companies.topup', $company) }}"
                  class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-3">
                @csrf
                <p class="text-[11px] uppercase tracking-wider font-bold text-slate-400">{{ __('ui.topup') }}</p>
                <input type="number" name="amount" min="1" value="{{ old('amount') }}"
                       placeholder="{{ __('ui.topup_amount') }}" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue/30 focus:border-sagara-blue">
                <input name="note" maxlength="255" value="{{ old('note') }}"
                       placeholder="{{ __('ui.topup_note') }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue/30 focus:border-sagara-blue">
                <button class="w-full px-4 py-2.5 rounded-xl text-sm font-bold bg-sagara-navy text-white shadow-md hover:opacity-90 transition">
                    {{ __('ui.topup') }}
                </button>
            </form>
        </div>

        {{-- KARYAWAN --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.employees') }}</h2>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-4 font-bold">{{ __('ui.full_name') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.email') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.department') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.position') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('ui.role') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($company->companyUsers as $member)
                                <tr class="hover:bg-sky-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-slate-900">{{ $member->displayName() }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $member->user->email }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $member->department ?? '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $member->position?->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap bg-sky-100 text-sagara-blue">{{ __('ui.role_' . $member->role_in_company) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LEDGER --}}
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.ledger') }}</h2>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
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
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap bg-slate-100 text-slate-600">{{ __('ui.ledger_type_' . $tx->type) }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ $tx->note }}
                                        @if ($tx->creator)
                                            <span class="text-xs text-slate-400">({{ $tx->creator->email }})</span>
                                        @endif
                                    </td>
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
        </div>
    </section>
@endsection