@extends('layouts.app')

@section('content')
    <section class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.payment') }}</h1>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <div class="font-mono text-xs font-bold text-sagara-blue mb-2">{{ $booking->booking_code }}</div>
            <h2 class="text-lg font-extrabold text-slate-900">{{ $booking->hotel_name }}</h2>
            <p class="text-sm text-slate-500">{{ $booking->room_name }}</p>
            <p class="text-sm text-slate-600 mt-3 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-sagara-blue"></i>
                {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
                ({{ __('ui.for_nights', ['count' => $booking->nights]) }})
            </p>

            <div class="mt-5 pt-5 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('ui.total_price') }}</div>
                <div class="text-3xl font-black text-sagara-blue mt-1">{{ format_price($booking->total_price, 'IDR') }}</div>
                <p class="text-[11px] text-slate-500 mt-1">{{ __('ui.price_estimate', ['amount' => $booking->displayTotal()]) }}</p>
            </div>

            <div class="mt-5 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-2">
                <i class="fa-regular fa-clock"></i> {{ __('ui.pay_before', ['time' => $booking->expires_at->format('H:i')]) }}
            </div>
        </div>

        <p class="text-[11px] text-slate-500 text-center"><i class="fa-solid fa-flask"></i> {{ __('ui.sandbox_notice') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <form method="POST" action="{{ route('bookings.pay.process', $booking) }}">
                @csrf
                <input type="hidden" name="outcome" value="success">
                <button class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> {{ __('ui.pay_success_btn') }}
                </button>
            </form>

            <form method="POST" action="{{ route('bookings.pay.process', $booking) }}">
                @csrf
                <input type="hidden" name="outcome" value="fail">
                <button class="w-full py-3.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-xmark"></i> {{ __('ui.pay_fail_btn') }}
                </button>
            </form>
        </div>
    </section>
@endsection