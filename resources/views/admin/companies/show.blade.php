@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md';
        $label = 'text-[11px] font-bold text-slate-400 uppercase tracking-wider';
        $chip  = 'inline-flex items-center px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold whitespace-nowrap';
        $th    = 'px-6 py-3 font-bold';
        $td    = 'px-6 py-3';
        $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.companies.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.companies') }}
        </a>

        {{-- HEADER --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words min-w-0">{{ $company->company_name }}</h1>
            @if ($company->corporate_code)
                <span class="{{ $chip }} font-mono">{{ $company->corporate_code }}</span>
            @endif
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM KIRI --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- RINGKASAN --}}
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="{{ $card }} p-6">
                        <p class="{{ $label }}"><i class="fa-solid fa-wallet text-sagara-blue"></i> {{ __('ui.deposit_balance') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900 break-words">{{ format_price($company->deposit_balance, 'IDR') }}</p>
                    </div>
                    <div class="{{ $card }} p-6">
                        <p class="{{ $label }}"><i class="fa-solid fa-plane-departure text-sagara-blue"></i> {{ __('ui.default_limit') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900 break-words">{{ format_price($company->travel_policy_limit, 'IDR') }}</p>
                    </div>
                </div>

                {{-- KARYAWAN --}}
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 mb-3">{{ __('ui.employees') }}</h2>
                    <div class="{{ $card }} overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="{{ $th }}">{{ __('ui.full_name') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.email') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.department') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.position') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.role') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($company->companyUsers as $member)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="{{ $td }} font-bold text-slate-900">{{ $member->displayName() }}</td>
                                            <td class="{{ $td }} text-slate-600">{{ $member->user->email }}</td>
                                            <td class="{{ $td }} text-slate-600">{{ $member->department ?? '-' }}</td>
                                            <td class="{{ $td }} text-slate-600">{{ $member->position?->name ?? '-' }}</td>
                                            <td class="{{ $td }}"><span class="{{ $chip }}">{{ __('ui.role_' . $member->role_in_company) }}</span></td>
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
                    <div class="{{ $card }} overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="{{ $th }}">{{ __('ui.date') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.status') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.amount') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.balance_after') }}</th>
                                        <th class="{{ $th }}">{{ __('ui.note') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($transactions as $tx)
                                        <tr class="align-top hover:bg-slate-50 transition">
                                            <td class="{{ $td }} text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="{{ $td }}"><span class="{{ $chip }}">{{ __('ui.ledger_type_' . $tx->type) }}</span></td>
                                            <td class="{{ $td }} font-bold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}
                                            </td>
                                            <td class="{{ $td }} text-slate-600 whitespace-nowrap">{{ format_price($tx->balance_after, 'IDR') }}</td>
                                            <td class="{{ $td }} min-w-48 text-slate-600">
                                                {{ $tx->note }}
                                                @if ($tx->creator)
                                                    <div class="text-[11px] text-slate-400">({{ $tx->creator->email }})</div>
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
            </div>

            {{-- KOLOM KANAN: TOP UP --}}
            <form method="POST" action="{{ route('admin.companies.topup', $company) }}"
                  class="{{ $card }} p-6 space-y-3 lg:sticky lg:top-24">
                @csrf
                <h2 class="font-extrabold text-slate-900">{{ __('ui.topup') }}</h2>
                <input type="number" name="amount" min="1" value="{{ old('amount') }}"
                       placeholder="{{ __('ui.topup_amount') }}" required class="{{ $input }}">
                <input name="note" maxlength="255" value="{{ old('note') }}"
                       placeholder="{{ __('ui.topup_note') }}" class="{{ $input }}">
                <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-plus"></i> {{ __('ui.topup') }}
                </button>
            </form>
        </div>
    </section>
@endsection