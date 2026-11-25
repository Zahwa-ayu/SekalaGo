@extends('layouts.app')

@section('content')
    @php
        $money   = fn ($value) => format_price($value, 'IDR');
        $current = $filters['range'];
        $keep    = request()->except(['range', 'from', 'to', 'page']);

        $delta = function (?float $value) {
            if ($value === null) {
                return '<span class="text-[11px] text-slate-400">' . e(__('ui.no_comparison')) . '</span>';
            }

            return '<span class="text-[11px] font-bold ' . ($value >= 0 ? 'text-emerald-600' : 'text-red-600') . '">'
                . '<i class="fa-solid ' . ($value >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down') . '"></i> '
                . ($value >= 0 ? '+' : '') . number_format($value, 1, ',', '.') . '%</span>';
        };

        $cards = [
            ['fa-sack-dollar', 'preport_earnings', 'preport_earnings_hint', $money($summary['earnings']), $delta($deltas['earnings']), 'bg-emerald-100 text-emerald-600'],
            ['fa-receipt', 'preport_gmv', 'preport_gmv_hint', $money($summary['gmv']), $delta($deltas['gmv']), 'bg-sky-100 text-sky-600'],
            ['fa-percent', 'preport_commission', 'preport_commission_hint', $money($summary['commission']), '', 'bg-violet-100 text-violet-600'],
            ['fa-clipboard-list', 'preport_orders', null, number_format($summary['orders'], 0, ',', '.'), $delta($deltas['orders']), 'bg-amber-100 text-amber-600'],
            ['fa-moon', 'preport_units', 'preport_units_hint', number_format($summary['units'], 0, ',', '.'), '', 'bg-teal-100 text-teal-600'],
            ['fa-ban', 'preport_cancel_fees', 'preport_cancel_fees_hint', $money($summary['cancel_fee_share']), '', 'bg-red-100 text-red-600'],
        ];

        $input = 'rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ __('ui.partner_reports') }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}
                    &middot; {{ __('ui.vs_previous', ['from' => $prevStart->format('d/m'), 'to' => $prevEnd->format('d/m/Y')]) }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('partner.reports.csv', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition"><i class="fa-solid fa-file-csv mr-1"></i> CSV</a>
                <a href="{{ route('partner.reports.pdf', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition"><i class="fa-solid fa-file-pdf mr-1"></i> PDF</a>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @foreach (['this_month', 'last_month', 'last_7', 'last_30', 'this_year'] as $preset)
                    <a href="{{ route('partner.reports.index', array_merge($keep, ['range' => $preset])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $current === $preset ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('ui.range_' . $preset) }}</a>
                @endforeach

                <form method="GET" action="{{ route('partner.reports.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="range" value="custom">
                    @if ($filters['product'])<input type="hidden" name="product" value="{{ $filters['product'] }}">@endif
                    <input type="date" name="from" required value="{{ $filters['from'] ?? $start->toDateString() }}" class="{{ $input }}">
                    <span class="text-slate-400 text-xs">-</span>
                    <input type="date" name="to" required value="{{ $filters['to'] ?? $end->toDateString() }}" class="{{ $input }}">
                    <button class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $current === 'custom' ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('ui.range_custom') }}</button>
                </form>
            </div>

            <x-filter-tabs param="product" :current="$filters['product'] ?? ''" :options="['' => __('ui.all'), 'hotel' => __('ui.tab_hotels'), 'car' => __('ui.tab_cars')]" />

            @if ($errors->any())
                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </div>

        {{-- KARTU ANGKA --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($cards as [$icon, $label, $hint, $value, $change, $tone])
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 leading-tight">{{ __('ui.' . $label) }}</div>
                    </div>
                    <div class="text-lg xl:text-xl font-black text-slate-900 break-words">{{ $value }}</div>
                    <div class="mt-auto">
                        {!! $change !!}
                        @if ($hint)<div class="text-[10px] text-slate-400 leading-snug">{{ __('ui.' . $hint) }}</div>@endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm text-emerald-900 flex flex-wrap items-center justify-between gap-2">
            <span class="font-bold">{{ __('ui.preport_income_total') }}</span>
            <span class="text-lg font-black">{{ $money($summary['income']) }}</span>
        </div>

        @if ($summary['late_fees'] > 0)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-clock"></i>
                {{ __('ui.preport_late_fees', ['amount' => $money($summary['late_fees']), 'count' => $summary['late_fee_count']]) }}
            </div>
        @endif

        @if ($summary['orders'] === 0 && $summary['cancel_fee_count'] === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-400">
                <i class="fa-solid fa-chart-column text-3xl mb-2 block"></i> {{ __('ui.analytics_empty') }}
            </div>
        @endif

        {{-- GRAFIK DAN PENCAIRAN --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <h2 class="font-extrabold text-slate-900 mb-4">{{ __('ui.preport_trend') }}</h2>
                <div class="h-64"><canvas id="partner-chart"></canvas></div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-slate-900">{{ __('ui.preport_payouts_title') }}</h2>
                    <a href="{{ route('partner.payouts.index') }}" class="text-xs font-bold text-sky-600 hover:underline">{{ __('ui.view') }}</a>
                </div>
                @foreach ([['preport_ready', $payouts['ready'], 'text-emerald-600'], ['preport_waiting', $payouts['waiting'], 'text-slate-700'], ['preport_pending', $payouts['pending'], 'text-amber-600'], ['preport_transferred', $payouts['transferred'], 'text-sky-600']] as [$label, $value, $tone])
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-slate-500">{{ __('ui.' . $label) }}</span>
                        <strong class="{{ $tone }}">{{ $money($value) }}</strong>
                    </div>
                @endforeach

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <span class="text-slate-500">{{ __('ui.preport_rating') }}</span>
                    <a href="{{ route('partner.reviews.index') }}" class="font-bold text-slate-900 hover:underline">
                        @if ($rating['count'] > 0)<i class="fa-solid fa-star text-amber-400"></i> {{ number_format($rating['average'], 1, ',', '.') }} <span class="font-medium text-slate-400">({{ $rating['count'] }})</span>@else - @endif
                    </a>
                </div>
                @if ($rating['in_period'] > 0)
                    <div class="text-[11px] text-slate-400 text-right">{{ __('ui.preport_new_reviews', ['count' => $rating['in_period']]) }}</div>
                @endif
            </div>
        </div>

        {{-- PER HOTEL DAN ARMADA --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.preport_by_item') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.preport_col_item') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.col_orders') }}</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.preport_col_units') }}</th>
                            <th class="text-right px-3 py-2.5">GMV</th>
                            <th class="text-right px-6 py-2.5">{{ __('ui.preport_earnings') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-6 py-2.5 font-semibold text-slate-800">
                                    <i class="fa-solid {{ $item->type === 'car' ? 'fa-car-side' : 'fa-hotel' }} text-sky-500 mr-1"></i>{{ $item->name }}
                                </td>
                                <td class="px-3 py-2.5 text-right text-slate-600">{{ $item->orders }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600">{{ $item->units }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600">{{ $money($item->gmv) }}</td>
                                <td class="px-6 py-2.5 text-right font-bold text-slate-900">{{ $money($item->earnings) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">{{ __('ui.analytics_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CARA MEMBACA --}}
        <div class="rounded-2xl bg-sky-50 border border-sky-100 px-6 py-5 text-xs text-sky-900 space-y-1.5">
            <div class="font-extrabold text-sm"><i class="fa-solid fa-circle-info"></i> {{ __('ui.analytics_notes_title') }}</div>
            <p><strong>{{ __('ui.preport_earnings') }}</strong>: {{ __('ui.preport_note_earnings') }}</p>
            <p><strong>{{ __('ui.preport_cancel_fees') }}</strong>: {{ __('ui.preport_note_cancel') }}</p>
            <p><strong>{{ __('ui.note_date_title') }}</strong>: {{ __('ui.preport_note_date') }}</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const series  = @json($series);
            const rupiah  = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
            const compact = (value) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);

            new Chart(document.getElementById('partner-chart'), {
                data: {
                    labels: series.labels,
                    datasets: [
                        { type: 'bar', label: @json(__('ui.preport_earnings')), data: series.earnings, backgroundColor: 'rgba(16,185,129,.35)', borderColor: 'rgba(16,185,129,.9)', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                        { type: 'line', label: @json(__('ui.preport_orders')), data: series.orders, borderColor: '#0ea5e9', backgroundColor: '#0ea5e9', tension: .3, pointRadius: series.labels.length > 40 ? 0 : 3, yAxisID: 'y1' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { tooltip: { callbacks: { label: (item) => item.dataset.type === 'line' ? item.dataset.label + ': ' + item.parsed.y : item.dataset.label + ': ' + rupiah(item.parsed.y) } } },
                    scales: {
                        y:  { beginAtZero: true, ticks: { callback: compact } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } },
                    },
                },
            });
        })();
    </script>
@endpush