@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'  => 'bg-amber-100 text-amber-700',
            'paid'     => 'bg-emerald-100 text-emerald-700',
            'failed'   => 'bg-red-100 text-red-700',
            'expired'  => 'bg-slate-100 text-slate-500',
            'refunded' => 'bg-sky-100 text-sky-700',
        ];
        $tabs = [null => 'all', 'refund' => 'payments_tab_refund', 'failed' => 'payments_tab_failed'];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.payments') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.payments_hint') }}</p>
        </div>

        @if ($driver !== 'midtrans')
            <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-flask"></i> {{ __('ui.payments_mock_mode') }}
            </div>
        @endif

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- BIAYA GATEWAY --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.gateway_fee_month') }}</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $money($fees['month']) }}</div>
            </div>
            <div class="bg-white rounded-2xl border {{ $fees['estimated'] > 0 ? 'border-amber-200' : 'border-slate-100' }} shadow-md p-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.gateway_fee_estimated') }}</div>
                <div class="text-xl font-black {{ $fees['estimated'] > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">{{ $fees['estimated'] }}</div>
                <div class="text-[11px] text-slate-500">{{ __('ui.gateway_fee_estimated_hint') }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $value => $label)
                <a href="{{ route('admin.payments.index', $value ? ['view' => $value] : []) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ (string) $view === (string) $value ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('ui.' . $label) }}
                    @if ($value === 'refund' && $manualCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px]">{{ $manualCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('ui.payment_order') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.payment_method') }}</th>
                            <th class="text-right px-3 py-3">{{ __('ui.payout_amount') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.kpi_gateway') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.account_status') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.payout_created_on') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            @php $settled = in_array($payment->status, ['paid', 'refunded'], true); @endphp
                            <tr class="hover:bg-slate-50/60 align-top">
                                <td class="px-6 py-3">
                                    <div class="font-mono text-xs font-bold text-slate-800">{{ $payment->order_id }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $payment->booking?->hotel_name }}</div>
                                </td>
                                <td class="px-3 py-3 text-slate-600 text-xs">{{ $payment->payment_type ?: '-' }}</td>
                                <td class="px-3 py-3 text-right font-bold text-slate-900">{{ $money($payment->amount) }}</td>

                                <td class="px-3 py-3">
                                    @if ($settled)
                                        <form method="POST" action="{{ route('admin.payments.fee', $payment) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            <input type="number" name="gateway_fee" min="0" max="{{ $payment->amount }}" required value="{{ $payment->gateway_fee }}"
                                                   class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                                            <button class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-[11px] font-bold transition">{{ __('ui.gateway_fee_save') }}</button>
                                        </form>
                                        <div class="mt-1 text-[10px] font-bold {{ $payment->gateway_fee_source === 'actual' ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $payment->gateway_fee_source === 'actual' ? __('ui.gateway_fee_actual') : __('ui.gateway_fee_estimate') }}
                                        </div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $statusStyle[$payment->status] ?? 'bg-slate-100 text-slate-600' }}">{{ __('ui.payment_status_' . $payment->status) }}</span>
                                    @if ($payment->refund_status === 'manual_pending')
                                        <div class="mt-1 text-[11px] font-bold text-rose-600">{{ __('ui.refund_manual_pending') }}</div>
                                    @elseif ($payment->refund_reference)
                                        <div class="mt-1 text-[11px] text-slate-500">{{ $payment->refund_reference }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3">
                                    @if ($payment->refund_status === 'manual_pending')
                                        <form method="POST" action="{{ route('admin.payments.refunded', $payment) }}" class="flex flex-col sm:flex-row gap-2">
                                            @csrf
                                            <input name="reference" required maxlength="100" placeholder="{{ __('ui.refund_reference_placeholder') }}"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-xs w-48 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                                            <button class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition whitespace-nowrap">{{ __('ui.refund_mark_done') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.no_results') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">{{ $payments->links() }}</div>
        </div>
    </section>
@endsection