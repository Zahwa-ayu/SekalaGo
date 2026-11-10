@extends('layouts.app')

@section('content')
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-sky-900 pt-24 pb-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-bold text-sky-300 hover:text-white transition">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ $booking->booking_code }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">{{ __('ui.extension_title') }}</h1>
        </div>
    </div>

    <section class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-6">
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm shadow-md">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-slate-100 shadow-lg p-6 sm:p-8 space-y-5">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $booking->hotel_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}
                    &middot; {{ __('ui.due_at_label') }} {{ $booking->due_at->format('d/m/Y H:i') }}
                </p>
            </div>

            {{-- Pilih jumlah hari --}}
            <form method="GET" action="{{ route('bookings.extend', $booking) }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('ui.extension_days') }}</label>
                    <select name="days" onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                        @foreach (range(1, \App\Services\ExtensionService::MAX_EXTENSION_DAYS) as $option)
                            <option value="{{ $option }}" @selected($option === $days)>{{ __('ui.for_days', ['count' => $option]) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="grid sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.extension_new_return') }}</div>
                    <div class="font-bold text-slate-900">{{ $quote['new_check_out']->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.extension_new_due') }}</div>
                    <div class="font-bold text-slate-900">{{ $quote['new_due_at']->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.extension_amount') }}</div>
                    <div class="text-xl font-black text-slate-900">{{ format_price($quote['amount']) }}</div>
                    <div class="text-xs text-slate-500">{{ __('ui.charged_in_idr', ['amount' => format_price($quote['amount'], 'IDR')]) }}</div>
                </div>
            </div>

            <p class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-sky-500"></i> {{ __('ui.extension_note') }}
            </p>
        </div>

        @if ($quote['available'])
            <form method="POST" action="{{ route('bookings.extend.store', $booking) }}"
                  class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 flex flex-wrap items-center gap-3">
                @csrf
                <input type="hidden" name="days" value="{{ $days }}">

                @if ($isDeposit)
                    <button class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md transition">
                        <i class="fa-solid fa-building-columns mr-1"></i> {{ __('ui.extension_pay_deposit') }}
                    </button>
                @else
                    <button name="outcome" value="success"
                            class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md transition">
                        <i class="fa-solid fa-credit-card mr-1"></i> {{ __('ui.extension_pay_now') }}
                    </button>
                    <button name="outcome" value="fail"
                            class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">
                        {{ __('ui.simulate_failure') }}
                    </button>
                @endif
            </form>
        @else
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm shadow-md">
                {{ __('ui.extension_unavailable') }}
            </div>
        @endif
    </section>
@endsection