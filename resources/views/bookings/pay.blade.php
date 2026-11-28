@extends('layouts.app')

@section('content')
    {{-- BANNER BIRU --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm">{{ __('ui.payment') }}</h1>
        </div>
    </div>

    <section class="relative w-full px-4 sm:px-6 lg:px-8 -mt-12 pb-12">
        <div class="w-full bg-white rounded-3xl border border-slate-100 shadow-xl p-6 sm:p-8 space-y-6">

            {{-- DATA PESANAN --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0">
                    <div class="font-mono text-xs font-bold text-sky-600 mb-2 break-all">{{ $booking->booking_code }}</div>
                    <h2 class="text-xl font-extrabold text-slate-900 break-words">{{ $booking->hotel_name }}</h2>
                    <p class="text-sm text-slate-500 break-words">{{ $booking->room_name }}</p>
                </div>

                <div class="flex items-start gap-3 text-sm shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</div>
                        <div class="font-bold text-slate-900">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</div>
                        <div class="text-xs text-slate-500">{{ __('ui.for_nights', ['count' => $booking->nights]) }}</div>
                    </div>
                </div>
            </div>

            {{-- TOTAL HARGA --}}
            <div class="rounded-2xl bg-sky-50 border border-sky-100 px-5 py-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.total_price') }}</div>
                <div class="text-3xl font-black text-slate-900 mt-1">{{ format_price($booking->total_price, 'IDR') }}</div>
                <p class="text-[11px] text-slate-500 mt-1">{{ __('ui.price_estimate', ['amount' => $booking->displayTotal()]) }}</p>
            </div>

            {{-- BATAS PEMBAYARAN --}}
            <div class="px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-semibold flex items-center gap-2">
                <i class="fa-regular fa-clock"></i> {{ __('ui.pay_before', ['time' => $booking->expires_at->format('H:i')]) }}
            </div>

            {{-- PEMBAYARAN --}}
            <div class="pt-6 border-t border-slate-100 space-y-4">
                @if (config('sekalago.payment_driver') === 'midtrans')
                    @include('bookings._pay-midtrans')
                @else
                    <p class="text-xs text-slate-500 text-center"><i class="fa-solid fa-flask"></i> {{ __('ui.sandbox_notice') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <form method="POST" action="{{ route('bookings.pay.process', $booking) }}">
                            @csrf
                            <input type="hidden" name="outcome" value="success">
                            <button class="w-full px-5 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-check"></i> {{ __('ui.pay_success_btn') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('bookings.pay.process', $booking) }}">
                            @csrf
                            <input type="hidden" name="outcome" value="fail">
                            <button class="w-full px-5 py-3 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-xmark"></i> {{ __('ui.pay_fail_btn') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection