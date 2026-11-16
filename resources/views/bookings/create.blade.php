@extends('layouts.app')

@section('content')
    {{-- BANNER BIRU (senada dengan hero Beranda) --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ url()->previous() }}" class="text-xs font-bold text-sky-100 hover:text-white transition">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.back') }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm">{{ __('ui.order_summary') }}</h1>
        </div>
    </div>

    <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-12 space-y-6">

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm shadow-md">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">

            {{-- KOLOM KIRI: FORM DATA PEMESAN (2/3 lebar). Di layar kecil tampil setelah ringkasan. --}}
            <div class="lg:col-span-2 order-2 lg:order-1 bg-white rounded-3xl border border-slate-100 shadow-xl p-6 sm:p-8 space-y-4">
                @if ($isOpen)
                    <form method="POST" action="{{ route('bookings.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="room_type" value="{{ $roomType->id }}">
                        <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
                        <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
                        <input type="hidden" name="guests" value="{{ $guests }}">

                        @include('partials.booking-details-fields', ['type' => 'hotel', 'isCorporate' => $isCorporate])

                        <button type="submit"
                                class="w-full px-5 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition">
                            @if ($isCorporate)
                                {{ $needsApproval ? __('ui.submit_for_approval') : __('ui.confirm_booking') }}
                            @else
                                <i class="fa-solid fa-credit-card mr-1"></i> {{ __('ui.continue_to_payment') }}
                            @endif
                        </button>
                    </form>
                @else
                    <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm font-semibold">
                        {{ __('ui.room_not_available') }}
                    </div>
                    <a href="{{ url()->previous() }}"
                       class="block text-center px-5 py-3 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-700 text-sm font-bold transition">
                        {{ __('ui.change_dates') }}
                    </a>
                @endif
            </div>

            {{-- KOLOM KANAN: RINGKASAN MENGINAP (1/3 lebar), menempel saat halaman di-scroll --}}
            <div class="order-1 lg:order-2 lg:sticky lg:top-24 bg-white rounded-3xl border border-slate-100 shadow-xl p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">{{ $roomType->hotel->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $roomType->name }}</p>
                </div>

                <div class="space-y-5 text-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0"><i class="fa-solid fa-calendar-days"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</div>
                            <div class="font-bold text-slate-900">{{ $checkIn->format('d/m/Y') }} - {{ $checkOut->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-500">{{ __('ui.for_nights', ['count' => $nights]) }}, {{ $guests }} {{ __('ui.guests') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0"><i class="fa-solid fa-bed"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.room') }}</div>
                            <div class="font-bold text-slate-900">{{ $roomType->name }}</div>
                            <div class="text-xs text-slate-500">{{ __('ui.max_guests', ['count' => $roomType->capacity]) }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0"><i class="fa-solid fa-tag"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.price_per_night') }}</div>
                            <div class="font-bold text-slate-900">{{ format_price($roomType->base_price, 'IDR') }}</div>
                            <div class="text-xs text-slate-500">&times; {{ $nights }} {{ __('ui.nights') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 pt-5 border-t border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0"><i class="fa-solid fa-coins"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.total_price') }}</div>
                            <div class="text-xl font-black text-slate-900">{{ format_price($total, 'IDR') }}</div>
                        </div>
                    </div>
                </div>

                {{-- INFO KEBIJAKAN PERUSAHAAN (khusus user corporate) --}}
                @if ($isCorporate)
                    @if ($needsApproval)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-700 px-4 py-3 text-xs">
                            <i class="fa-regular fa-clock mr-1"></i>
                            {{ __('ui.over_limit_notice') }}
                            @if ($limit !== null)
                                <div class="mt-1 text-slate-600">
                                    {{ __('ui.your_limit') }}: <strong>{{ format_price($limit, 'IDR') }}</strong>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-xs">
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            {{ __('ui.within_limit_notice') }}
                            @if ($limit !== null)
                                <div class="mt-1 text-slate-600">
                                    {{ __('ui.your_limit') }}: <strong>{{ format_price($limit, 'IDR') }}</strong>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>
@endsection