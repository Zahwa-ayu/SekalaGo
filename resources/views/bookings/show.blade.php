@extends('layouts.app')

@section('content')
    @php
        $badge = match ($booking->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-700',
            'pending_payment' => 'bg-amber-100 text-amber-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
        $payBadge = match ($booking->payment_status) {
            'paid', 'success', 'settled' => 'bg-emerald-100 text-emerald-700',
            'failed', 'refunded' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_bookings') }}
        </a>

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono">{{ $booking->booking_code }}</h1>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $badge }}">{{ __('ui.booking_status_' . $booking->status) }}</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-6">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $booking->hotel_name }}</h2>
                <p class="text-sm text-slate-500">{{ $booking->room_name }}</p>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-calendar-days text-sagara-blue"></i> {{ __('ui.check_in') }} - {{ __('ui.check_out') }}</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
                        <span class="text-slate-500 font-normal">({{ __('ui.for_nights', ['count' => $booking->nights]) }})</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-users text-sagara-blue"></i> {{ __('ui.guests') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $booking->guests }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-user text-sagara-blue"></i> {{ __('ui.guest_name') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $booking->guest_name }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-credit-card text-sagara-blue"></i> {{ __('ui.payment') }}</dt>
                    <dd>
                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $payBadge }}">{{ __('ui.payment_status_' . $booking->payment_status) }}</span>
                        @if ($booking->payment_reference)
                            <span class="font-mono text-[11px] text-slate-500 ml-1">({{ $booking->payment_reference }})</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="pt-5 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('ui.total_price') }}</div>
                <div class="text-3xl font-black text-sagara-blue mt-1">{{ $booking->displayTotal() }}</div>
                <p class="text-[11px] text-slate-500 mt-1"><i class="fa-solid fa-circle-info"></i> {{ __('ui.charged_in_idr', ['amount' => format_price($booking->total_price, 'IDR')]) }}</p>
            </div>

            @if ($booking->status === 'pending_payment')
                <a href="{{ route('bookings.pay', $booking) }}"
                   class="w-full py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> {{ __('ui.pay_now') }}
                </a>
            @endif
        </div>
    </section>
@endsection