@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.reports') }}</h1>
                <p class="text-sm text-slate-500">{{ $company->company_name }}</p>
            </div>

            <form method="GET" action="{{ route('corporate.reports.index') }}" class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('ui.report_month') }}</label>
                <select name="month" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                    @foreach ($months as $m)
                        <option value="{{ $m->format('Y-m') }}" @selected($m->format('Y-m') === $month->format('Y-m'))>
                            {{ $m->format('m/Y') }}
                        </option>
                    @endforeach
                </select>
                <noscript><button class="px-4 py-2 rounded-xl bg-sagara-navy text-white text-sm font-bold">{{ __('ui.show') }}</button></noscript>
            </form>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- UNDUH --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('corporate.reports.csv', ['month' => $month->format('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fa-solid fa-file-csv text-emerald-600"></i> {{ __('ui.download_csv') }}
            </a>
            <a href="{{ route('corporate.reports.pdf', ['month' => $month->format('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fa-solid fa-file-pdf text-red-600"></i> {{ __('ui.download_pdf_report') }}
            </a>
        </div>

        {{-- RINGKASAN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['fa-receipt', __('ui.orders_count'), $summary['orders'], false],
                ['fa-coins', __('ui.total_spend'), format_price($summary['total'], 'IDR'), true],
                ['fa-scale-balanced', __('ui.average_per_order'), format_price($summary['average'], 'IDR'), false],
                ['fa-moon', __('ui.nights_total'), $summary['nights'], false],
            ] as [$icon, $label, $value, $highlight])
                <div class="{{ $highlight ? 'bg-gradient-to-br from-sagara-navy to-sagara-blue text-white shadow-xl' : 'bg-white border border-slate-100 shadow-md' }} rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-3 {{ $highlight ? 'bg-white/15 text-sagara-teal' : 'bg-sky-100 text-sagara-blue' }}">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ $highlight ? 'text-sky-200' : 'text-slate-400' }}">{{ $label }}</div>
                    <div class="text-xl sm:text-2xl font-black mt-1 {{ $highlight ? '' : 'text-slate-900' }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-slate-500">
            {{ __('ui.booking_status_cancelled') }}: <strong>{{ $summary['cancelled'] }}</strong>
            ({{ __('ui.refunds') }}: {{ format_price($summary['refunded'], 'IDR') }}) &middot;
            {{ __('ui.booking_status_rejected') }}: <strong>{{ $summary['rejected'] }}</strong> &middot;
            {{ __('ui.booking_status_pending_approval') }}: <strong>{{ $summary['pending'] }}</strong>
        </p>
        <p class="text-[11px] text-slate-400"><i class="fa-solid fa-circle-info"></i> {{ __('ui.report_note') }}</p>

        @if ($summary['orders'] === 0)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-12 text-center text-slate-400">
                <i class="fa-solid fa-chart-column text-3xl mb-2 block"></i>
                {{ __('ui.no_report_data') }}
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- PER DEPARTEMEN --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 font-extrabold text-slate-900">{{ __('ui.by_department') }}</div>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3 font-bold">{{ __('ui.department') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.orders_count') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.total_spend') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($byDepartment as $row)
                                <tr>
                                    <td class="px-6 py-3 font-semibold text-slate-800">{{ $row['department'] }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $row['orders'] }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-900 whitespace-nowrap">{{ format_price($row['total'], 'IDR') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PER KARYAWAN --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 font-extrabold text-slate-900">{{ __('ui.by_employee') }}</div>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3 font-bold">{{ __('ui.employees') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.orders_count') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.total_spend') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.budget_used') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($byEmployee as $row)
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row['name'] }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $row['department'] }} / {{ $row['position'] }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600">{{ $row['orders'] }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-900 whitespace-nowrap">{{ format_price($row['total'], 'IDR') }}</td>
                                    <td class="px-6 py-3 w-40">
                                        @if ($row['budget'] !== null)
                                            <div class="text-[11px] text-slate-500 mb-1">
                                                {{ $row['used_percent'] }}% / {{ format_price($row['budget'], 'IDR') }}
                                            </div>
                                            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full {{ $row['used_percent'] > 100 ? 'bg-red-500' : 'bg-sagara-teal' }}"
                                                     style="width: {{ min(100, $row['used_percent']) }}%"></div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- RINCIAN --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 font-extrabold text-slate-900">{{ __('ui.booking_detail') }}</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3 font-bold">{{ __('ui.booking_code') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.booked_on') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.employees') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.hotel') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __('ui.total_price') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($rows as $booking)
                                <tr>
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-sagara-blue whitespace-nowrap">{{ $booking->booking_code }}</td>
                                    <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $booking->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-slate-800">{{ $booking->user->companyUser?->displayName() ?? $booking->user->email }}</td>
                                    <td class="px-6 py-3 text-slate-800">{{ $booking->hotel_name }} <span class="text-[11px] text-slate-500">/ {{ $booking->room_name }}</span></td>
                                    <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-900 whitespace-nowrap">{{ format_price($booking->total_price, 'IDR') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection