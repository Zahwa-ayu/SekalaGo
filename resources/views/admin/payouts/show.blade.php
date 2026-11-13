@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('admin.payouts.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.payouts') }}
            </a>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono">{{ $payout->code }}</h1>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyle[$payout->status] }}">{{ __('ui.payout_status_' . $payout->status) }}</span>
            </div>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- RINGKASAN TRANSFER --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 grid sm:grid-cols-2 gap-6">
            <div class="space-y-3 text-sm">
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_transfer_to') }}</h2>
                <div class="font-extrabold text-slate-900 text-lg">{{ $payout->partner->company_name }}</div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 font-mono text-sm text-slate-800 break-all">{{ $payout->bank_account ?: '-' }}</div>
                <div class="text-xs text-slate-500">
                    {{ __('ui.payout_schedule') }}: {{ $payout->schedule ? __('ui.schedule_' . $payout->schedule) : '-' }}
                    &middot; {{ __('ui.payout_created_by', ['name' => $payout->creator?->email ?? '-', 'date' => $payout->created_at->format('d/m/Y H:i')]) }}
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.payout_amount') }}</h2>
                <div class="text-3xl font-black text-slate-900">{{ $money($payout->total_amount) }}</div>
                <div class="text-xs text-slate-500">
                    {{ $payout->booking_count }} {{ __('ui.col_orders') }}
                    &middot; {{ __('ui.kpi_commission') }}: {{ $money($payout->platform_fee_total) }}
                </div>

                @if ($payout->status === 'paid')
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-800 space-y-0.5">
                        <div><strong>{{ __('ui.payout_reference') }}:</strong> {{ $payout->transfer_reference }}</div>
                        <div>{{ $payout->paid_at->format('d/m/Y H:i') }} &middot; {{ $payout->payer?->email }}</div>
                    </div>
                @elseif ($payout->status === 'cancelled')
                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600">
                        <strong>{{ __('ui.payout_cancel_reason') }}:</strong> {{ $payout->note }}
                    </div>
                @endif
            </div>
        </div>

        {{-- AKSI (hanya saat menunggu transfer) --}}
        @if ($payout->status === 'pending')
            <div class="grid md:grid-cols-2 gap-6">
                <form method="POST" action="{{ route('admin.payouts.paid', $payout) }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
                    @csrf
                    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> {{ __('ui.payout_mark_paid') }}</h2>

                    <div>
                        <label class="{{ $label }}">{{ __('ui.payout_reference') }}</label>
                        <input name="transfer_reference" required maxlength="100" value="{{ old('transfer_reference') }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.payout_paid_on') }}</label>
                        <input type="date" name="paid_on" max="{{ today()->toDateString() }}" value="{{ old('paid_on', today()->toDateString()) }}" class="{{ $input }}">
                    </div>

                    <button class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition">
                        {{ __('ui.payout_mark_paid') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.payouts.cancel', $payout) }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4"
                      onsubmit="return confirm(@js(__('ui.payout_cancel_confirm')))">
                    @csrf
                    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-ban text-red-500 mr-1"></i> {{ __('ui.payout_cancel') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('ui.payout_cancel_hint') }}</p>

                    <div>
                        <label class="{{ $label }}">{{ __('ui.payout_cancel_reason') }}</label>
                        <input name="reason" required maxlength="500" value="{{ old('reason') }}" class="{{ $input }}">
                    </div>

                    <button class="px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">
                        {{ __('ui.payout_cancel') }}
                    </button>
                </form>
            </div>
        @endif

        {{-- RINCIAN PESANAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 pt-6 pb-4 flex items-center justify-between gap-3">
                <h2 class="font-extrabold text-slate-900">{{ __('ui.booking_detail') }}</h2>
                <a href="{{ route('admin.payouts.csv', $payout) }}" class="text-sm font-bold text-sky-600 hover:underline">
                    <i class="fa-solid fa-file-csv mr-1"></i> {{ __('ui.analytics_csv') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-2.5">{{ __('ui.booking_code') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.item_name') }}</th>
                            <th class="text-left px-3 py-2.5">{{ __('ui.period_start') }} - {{ __('ui.period_end') }}</th>
                            <th class="text-right px-3 py-2.5">GMV</th>
                            <th class="text-right px-3 py-2.5">{{ __('ui.kpi_commission') }}</th>
                            <th class="text-right px-6 py-2.5">{{ __('ui.csv_vendor') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($bookings as $booking)
                            <tr>
                                <td class="px-6 py-2.5 font-mono text-xs text-slate-600">{{ $booking->booking_code }}</td>
                                <td class="px-3 py-2.5 text-slate-800">
                                    <i class="fa-solid {{ $booking->productIcon() }} text-sky-500 mr-1"></i>{{ $booking->hotel_name }}
                                    <span class="text-[11px] text-slate-500">/ {{ $booking->room_name }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-500">{{ $booking->check_in->format('d/m') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600">{{ $money($booking->total_price) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-500">{{ $money($booking->platform_fee) }}</td>
                                <td class="px-6 py-2.5 text-right font-bold text-slate-900">{{ $money($booking->vendor_revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection