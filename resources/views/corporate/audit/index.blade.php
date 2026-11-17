@extends('layouts.app')

@section('content')
    @php
        $money   = fn ($value) => format_price($value, 'IDR');
        $signed  = fn ($value) => ($value > 0 ? '+' : '') . format_price($value, 'IDR');
        $presets = ['this_month', 'last_month', 'this_quarter', 'this_year'];
        $current = $filters['range'];
        $params  = $routes['params'];

        $verdictStyle = [
            'clean'     => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'fa-circle-check'],
            'attention' => ['border-amber-200 bg-amber-50 text-amber-800', 'fa-triangle-exclamation'],
            'issues'    => ['border-red-200 bg-red-50 text-red-800', 'fa-circle-xmark'],
        ][$verdict];

        $statusStyle = [
            'pass' => ['bg-emerald-100 text-emerald-700', 'fa-circle-check'],
            'fail' => ['bg-red-100 text-red-700', 'fa-circle-xmark'],
            'warn' => ['bg-amber-100 text-amber-700', 'fa-triangle-exclamation'],
            'info' => ['bg-sky-100 text-sky-700', 'fa-circle-info'],
        ];

        $cards = [
            ['audit_opening', $money($statement['opening']), 'text-slate-900'],
            ['audit_topups', $signed($statement['topups']), 'text-emerald-600'],
            ['audit_charges', $signed($statement['charges']), 'text-red-600'],
            ['audit_refunds', $signed($statement['refunds']), 'text-sky-600'],
            ['audit_closing', $money($statement['closing']), 'text-slate-900'],
        ];

        $counts = [
            'fail' => $checks->where('status', 'fail')->count(),
            'warn' => $checks->where('status', 'warn')->count(),
        ];
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.financial_audit') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $company->company_name }} &middot; {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route($routes['csv'], $params + request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition"><i class="fa-solid fa-file-csv mr-1"></i> CSV</a>
                <a href="{{ route($routes['pdf'], $params + request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition"><i class="fa-solid fa-file-pdf mr-1"></i> PDF</a>
            </div>
        </div>

        {{-- PERIODE --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($presets as $preset)
                    <a href="{{ route($routes['index'], $params + ['range' => $preset]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $current === $preset ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('ui.audit_range_' . $preset) }}
                    </a>
                @endforeach

                <form method="GET" action="{{ route($routes['index'], $params) }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="from" required value="{{ $filters['from'] ?? $start->toDateString() }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <span class="text-slate-400 text-xs">-</span>
                    <input type="date" name="to" required value="{{ $filters['to'] ?? $end->toDateString() }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <button class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $current === 'custom' ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('ui.range_custom') }}</button>
                </form>
            </div>

            @if ($errors->any())
                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </div>

        {{-- KESIMPULAN --}}
        <div class="rounded-2xl border {{ $verdictStyle[0] }} px-5 py-4 flex items-start gap-3">
            <i class="fa-solid {{ $verdictStyle[1] }} text-xl mt-0.5"></i>
            <div>
                <div class="font-extrabold">{{ __('ui.audit_verdict_' . $verdict) }}</div>
                <div class="text-sm">{{ __('ui.audit_verdict_summary', ['fail' => $counts['fail'], 'warn' => $counts['warn']]) }}</div>
            </div>
        </div>

        {{-- MUTASI SALDO --}}
        <div>
            <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.audit_statement') }}</h2>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                @foreach ($cards as [$label, $value, $tone])
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.' . $label) }}</div>
                        <div class="text-lg font-black {{ $tone }} break-words mt-1">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            @if ($statement['other'] !== 0)
                <p class="text-xs text-amber-700 mt-2"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.audit_other_movement', ['amount' => $signed($statement['other'])]) }}</p>
            @endif

            @if ($statement['closing'] !== $statement['closing_recorded'])
                <p class="text-xs text-red-700 mt-2"><i class="fa-solid fa-circle-xmark"></i> {{ __('ui.audit_closing_mismatch', ['computed' => $money($statement['closing']), 'recorded' => $money($statement['closing_recorded'])]) }}</p>
            @endif
        </div>

        {{-- PEMERIKSAAN OTOMATIS --}}
        <div class="space-y-3">
            <h2 class="font-extrabold text-slate-900">{{ __('ui.audit_checks') }}</h2>

            @foreach ($checks as $check)
                @php [$tone, $icon] = $statusStyle[$check['status']]; @endphp

                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5">
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-900">{{ __('ui.audit_check_' . $check['key']) }}</div>
                            <div class="text-xs text-slate-500">{{ __('ui.audit_check_' . $check['key'] . '_hint') }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $tone }}">
                            {{ $check['status'] === 'pass' ? __('ui.audit_pass') : __('ui.audit_items', ['count' => $check['items']->count()]) }}
                        </span>
                    </div>

                    @if ($check['items']->isNotEmpty())
                        <ul class="mt-3 divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden text-sm">
                            @foreach ($check['items']->take(20) as $item)
                                <li class="px-4 py-2.5 bg-slate-50/50">
                                    <div class="font-mono text-xs font-bold text-slate-700">{{ $item['label'] }}</div>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $item['detail'] }}</div>
                                </li>
                            @endforeach
                        </ul>
                        @if ($check['items']->count() > 20)
                            <p class="text-[11px] text-slate-400 mt-2">{{ __('ui.audit_more', ['count' => $check['items']->count() - 20]) }}</p>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        {{-- RINCIAN MUTASI --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.audit_ledger') }}</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.audit_col_date') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.audit_col_type') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.audit_col_reference') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.audit_col_note') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.audit_col_amount') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.audit_col_balance') }}</th>
                            <th class="text-left px-6 py-2.5">{{ __('ui.audit_col_by') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-slate-50/60 text-xs font-bold text-slate-600">
                            <td class="px-6 py-2" colspan="5">{{ __('ui.audit_opening') }}</td>
                            <td class="px-3 py-2 text-right">{{ $money($statement['opening']) }}</td>
                            <td></td>
                        </tr>
                        @forelse ($statement['rows'] as $tx)
                            <tr>
                                <td class="px-6 py-2 text-xs text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ __('ui.deposit_type_' . $tx->type) }}</td>
                                <td class="px-3 py-2 font-mono text-[11px] text-slate-500">{{ $tx->reference ?: '-' }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500 max-w-64 truncate" title="{{ $tx->note }}">{{ $tx->note ?: '-' }}</td>
                                <td class="px-3 py-2 text-right font-bold {{ $tx->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $signed($tx->amount) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $money($tx->balance_after) }}</td>
                                <td class="px-6 py-2 text-xs text-slate-500">{{ $tx->creator?->email ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs">{{ __('ui.audit_ledger_empty') }}</td></tr>
                        @endforelse
                        <tr class="bg-slate-50/60 text-xs font-bold text-slate-600">
                            <td class="px-6 py-2" colspan="5">{{ __('ui.audit_closing') }}</td>
                            <td class="px-3 py-2 text-right">{{ $money($statement['closing']) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-[11px] text-slate-400">{{ __('ui.audit_immutable_note') }}</p>
    </section>
@endsection