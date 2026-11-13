@extends('layouts.app')

@section('content')
    @php
        $presets = ['this_month', 'last_month', 'last_7', 'last_30', 'this_year'];
        $current = $filters['range'];
        $keep    = request()->except(['range', 'from', 'to', 'page']);
        $money   = fn ($value) => format_price($value, 'IDR');

        // Nilai tanggal untuk input filter (dipakai juga sebagai batas min/max)
        $fromValue = $filters['from'] ?? $start->toDateString();
        $toValue   = $filters['to'] ?? $end->toDateString();

        // Perubahan dibanding periode sebelumnya (inverse: kenaikan dianggap buruk, mis. refund)
        $delta = function (?float $value, bool $inverse = false) {
            if ($value === null) {
                return '<span class="text-[11px] text-slate-400">' . e(__('ui.no_comparison')) . '</span>';
            }

            $good = $inverse ? $value <= 0 : $value >= 0;

            return '<span class="text-[11px] font-bold ' . ($good ? 'text-emerald-600' : 'text-red-600') . '">'
                . '<i class="fa-solid ' . ($value >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down') . '"></i> '
                . ($value >= 0 ? '+' : '') . number_format($value, 1, ',', '.') . '%</span>';
        };

        $cards = [
            ['fa-sack-dollar', 'kpi_gmv', 'kpi_gmv_hint', $money($summary['gmv']), $delta($deltas['gmv']), 'bg-sky-100 text-sky-600'],
            ['fa-percent', 'kpi_commission', 'kpi_commission_hint', $money($summary['fee']), $delta($deltas['fee']), 'bg-emerald-100 text-emerald-600'],
            ['fa-receipt', 'kpi_orders', null, number_format($summary['orders'], 0, ',', '.'), $delta($deltas['orders']), 'bg-violet-100 text-violet-600'],
            ['fa-scale-balanced', 'kpi_average', null, $money($summary['average']), $delta($deltas['average']), 'bg-amber-100 text-amber-600'],
            ['fa-chart-pie', 'kpi_take_rate', 'kpi_take_rate_hint', number_format($summary['take_rate'], 1, ',', '.') . '%', '', 'bg-teal-100 text-teal-600'],
            ['fa-rotate-left', 'kpi_refunds', null, $money($summary['refunded']), $delta($deltas['refunded'], true), 'bg-red-100 text-red-600'],
        ];

        $segmentLabels = ['b2c' => __('ui.segment_b2c'), 'b2b' => __('ui.segment_b2b')];
        $productLabels = ['hotel' => __('ui.tab_hotels'), 'car' => __('ui.tab_cars')];
        $input = 'rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30';
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.analytics') }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}
                    &middot; {{ __('ui.vs_previous', ['from' => $prevStart->format('d/m'), 'to' => $prevEnd->format('d/m/Y')]) }}
                </p>
            </div>

            <a href="{{ route('admin.analytics.csv', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition">
                <i class="fa-solid fa-file-csv"></i> {{ __('ui.analytics_csv') }}
            </a>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($presets as $preset)
                    <a href="{{ route('admin.analytics.index', array_merge($keep, ['range' => $preset])) }}"
                       class="{{ 'px-3.5 py-2 rounded-xl text-xs font-bold transition' }} {{ $current === $preset ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('ui.range_' . $preset) }}
                    </a>
                @endforeach

                <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex flex-wrap items-center gap-2 ml-0 sm:ml-2">
                    <input type="hidden" name="range" value="custom">
                    @if ($filters['product'])<input type="hidden" name="product" value="{{ $filters['product'] }}">@endif

                    {{-- "from" tidak boleh melewati "to" --}}
                    <input type="date" id="analytics-from" name="from" required
                           value="{{ $fromValue }}"
                           max="{{ $toValue }}"
                           class="{{ $input }}" aria-label="{{ __('ui.analytics_from') }}">
                    <span class="text-slate-400 text-xs">-</span>
                    {{-- "to" tidak boleh sebelum "from" --}}
                    <input type="date" id="analytics-to" name="to" required
                           value="{{ $toValue }}"
                           min="{{ $fromValue }}"
                           class="{{ $input }}" aria-label="{{ __('ui.analytics_to') }}">

                    <button class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $current === 'custom' ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('ui.range_custom') }}
                    </button>
                </form>

                {{-- RESET FILTER: kembali ke tampilan default (tanpa parameter) --}}
                <a href="{{ route('admin.analytics.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition bg-white border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200">
                    <i class="fa-solid fa-rotate-left"></i> {{ __('ui.reset_filter') }}
                </a>
            </div>

            @include('partials.product-filter', ['route' => 'admin.analytics.index'])

            @if ($errors->any())
                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </div>

        {{-- KARTU ANGKA --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach ($cards as [$icon, $label, $hint, $value, $change, $tone])
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 leading-tight">{{ __('ui.' . $label) }}</div>
                    </div>
                    <div class="text-lg xl:text-xl font-black text-slate-900 break-words">{{ $value }}</div>
                    <div class="mt-auto">
                        {!! $change !!}
                        @if ($hint)
                            <div class="text-[10px] text-slate-400 leading-snug">{{ __('ui.' . $hint) }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($summary['refunds'] > 0)
            <p class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-sky-500"></i>
                {{ __('ui.kpi_refunds_hint', ['count' => $summary['refunds']]) }}
                &middot; {{ __('ui.cancel_rate') }}: <strong>{{ number_format($summary['cancel_rate'], 1, ',', '.') }}%</strong>
            </p>
        @endif

        @if ($summary['orders'] === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-400">
                <i class="fa-solid fa-chart-line text-3xl mb-2 block"></i>
                {{ __('ui.analytics_empty') }}
            </div>
        @endif

        {{-- GRAFIK --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <h2 class="font-extrabold text-slate-900 mb-4">{{ __('ui.chart_trend') }}</h2>
                <div class="h-72"><canvas id="trend-chart"></canvas></div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <h2 class="font-extrabold text-slate-900 mb-4">{{ __('ui.chart_product') }}</h2>
                <div class="h-52"><canvas id="product-chart"></canvas></div>

                <div class="mt-4 space-y-2 text-sm">
                    @foreach ($byProduct as $row)
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-slate-700">{{ $productLabels[$row['key']] ?? $row['key'] }}</span>
                            <span class="text-slate-500 text-xs">{{ $row['orders'] }} &middot; <strong class="text-slate-900">{{ $money($row['gmv']) }}</strong></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- B2C / B2B DAN INFORMASI TAMBAHAN --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
                <h2 class="font-extrabold text-slate-900">{{ __('ui.segment_title') }}</h2>

                @foreach (['b2c', 'b2b'] as $segment)
                    @php
                        $row   = $bySegment->get($segment, ['orders' => 0, 'gmv' => 0, 'fee' => 0]);
                        $share = $summary['gmv'] > 0 ? round($row['gmv'] / $summary['gmv'] * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-bold text-slate-800">{{ $segmentLabels[$segment] }}</span>
                            <span class="text-xs text-slate-500">
                                {{ $row['orders'] }} {{ __('ui.col_orders') }} &middot; <strong class="text-slate-900">{{ $money($row['gmv']) }}</strong>
                                &middot; {{ __('ui.kpi_commission') }} {{ $money($row['fee']) }}
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $segment === 'b2b' ? 'bg-violet-500' : 'bg-sky-500' }}" style="width: {{ $share }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-3 text-sm">
                <h2 class="font-extrabold text-slate-900">{{ __('ui.analytics_extra') }}</h2>

                <div class="flex justify-between gap-3">
                    <span class="text-slate-500">{{ __('ui.kpi_vendor') }}</span>
                    <strong class="text-slate-900">{{ $money($summary['vendor']) }}</strong>
                </div>

                @if ($lateFees)
                    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                        <i class="fa-solid fa-clock"></i>
                        {{ __('ui.late_fee_info', ['amount' => $money($lateFees['fees']), 'count' => $lateFees['rentals']]) }}
                    </div>
                @endif
            </div>
        </div>

        {{-- MITRA DAN KOTA TERATAS --}}
        <div class="grid lg:grid-cols-2 gap-6">
            @foreach ([['top_partners', $partners, 'col_partner'], ['top_cities', $cities, 'city_name']] as [$title, $rows, $firstColumn])
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <h2 class="px-6 pt-6 font-extrabold text-slate-900">{{ __('ui.' . $title) }}</h2>

                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="text-left px-6 py-2.5">{{ __('ui.' . $firstColumn) }}</th>
                                    <th class="text-right px-3 py-2.5">{{ __('ui.col_orders') }}</th>
                                    <th class="text-right px-3 py-2.5">GMV</th>
                                    <th class="text-right px-6 py-2.5">{{ __('ui.col_share') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($rows as $row)
                                    @php $share = $summary['gmv'] > 0 ? round($row['gmv'] / $summary['gmv'] * 100) : 0; @endphp
                                    <tr>
                                        <td class="px-6 py-2.5 font-semibold text-slate-800">{{ $row['key'] }}</td>
                                        <td class="px-3 py-2.5 text-right text-slate-600">{{ $row['orders'] }}</td>
                                        <td class="px-3 py-2.5 text-right font-bold text-slate-900">{{ $money($row['gmv']) }}</td>
                                        <td class="px-6 py-2.5 text-right text-slate-500">{{ $share }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs">{{ __('ui.analytics_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CARA MEMBACA ANGKA --}}
        <div class="rounded-2xl bg-sky-50 border border-sky-100 px-6 py-5 text-xs text-sky-900 space-y-1.5">
            <div class="font-extrabold text-sm"><i class="fa-solid fa-circle-info"></i> {{ __('ui.analytics_notes_title') }}</div>
            <p><strong>GMV</strong>: {{ __('ui.note_gmv') }}</p>
            <p><strong>{{ __('ui.kpi_commission') }}</strong>: {{ __('ui.note_commission') }}</p>
            <p><strong>{{ __('ui.note_date_title') }}</strong>: {{ __('ui.note_date') }}</p>
            <p><strong>Refund</strong>: {{ __('ui.note_refund') }}</p>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Batasi input tanggal: "from" tidak boleh setelah "to", "to" tidak boleh sebelum "from"
        (function () {
            const from = document.getElementById('analytics-from');
            const to   = document.getElementById('analytics-to');
            if (!from || !to) return;

            const sync = () => {
                to.min   = from.value || '';
                from.max = to.value || '';
            };

            from.addEventListener('change', sync);
            to.addEventListener('change', sync);
            sync();
        })();

        (function () {
            const series = @json($series);
            const products = @json($byProduct->map(fn ($row) => ['label' => $productLabels[$row['key']] ?? $row['key'], 'gmv' => $row['gmv']])->values());
            const labels = { gmv: @json('GMV'), fee: @json(__('ui.kpi_commission')) };

            const rupiah  = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
            const compact = (value) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);

            new Chart(document.getElementById('trend-chart'), {
                data: {
                    labels: series.labels,
                    datasets: [
                        { type: 'bar', label: labels.gmv, data: series.gmv, backgroundColor: 'rgba(14,165,233,.35)', borderColor: 'rgba(14,165,233,.9)', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                        { type: 'line', label: labels.fee, data: series.fee, borderColor: '#10b981', backgroundColor: '#10b981', tension: .3, pointRadius: series.labels.length > 40 ? 0 : 3, yAxisID: 'y1' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { tooltip: { callbacks: { label: (item) => item.dataset.label + ': ' + rupiah(item.parsed.y) } } },
                    scales: {
                        y:  { beginAtZero: true, ticks: { callback: compact } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: compact } },
                    },
                },
            });

            new Chart(document.getElementById('product-chart'), {
                type: 'doughnut',
                data: {
                    labels: products.map((p) => p.label),
                    datasets: [{ data: products.map((p) => p.gmv), backgroundColor: ['#0ea5e9', '#8b5cf6', '#10b981'], borderWidth: 0 }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: (item) => item.label + ': ' + rupiah(item.parsed) } } },
                },
            });
        })();
    </script>
@endpush