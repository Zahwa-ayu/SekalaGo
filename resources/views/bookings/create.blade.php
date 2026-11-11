@extends('layouts.app')

@section('content')
    {{-- BANNER GELAP --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-sky-900 pt-24 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ url()->previous() }}" class="text-xs font-bold text-sky-300 hover:text-white transition">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.back') }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">{{ __('ui.order_summary') }}</h1>
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

            {{-- KOLOM KIRI: RINGKASAN MENGINAP (2/3 lebar) --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-lg p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">{{ $roomType->hotel->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $roomType->name }}</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6 text-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-calendar-days"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.check_in') }} - {{ __('ui.check_out') }}</div>
                            <div class="font-bold text-slate-900">{{ $checkIn->format('d/m/Y') }} - {{ $checkOut->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-500">{{ __('ui.for_nights', ['count' => $nights]) }}, {{ $guests }} {{ __('ui.guests') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-bed"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.room') }}</div>
                            <div class="font-bold text-slate-900">{{ $roomType->name }}</div>
                            <div class="text-xs text-slate-500">{{ __('ui.max_guests', ['count' => $roomType->capacity]) }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-tag"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.price_per_night') }}</div>
                            <div class="font-bold text-slate-900">{{ format_price($roomType->base_price, 'IDR') }}</div>
                            <div class="text-xs text-slate-500">&times; {{ $nights }} {{ __('ui.nights') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-coins"></i></div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.total_price') }}</div>
                            <div class="text-xl font-black text-slate-900">{{ format_price($total, 'IDR') }}</div>
                        </div>
                    </div>
                </div>

                {{-- INFO KEBIJAKAN PERUSAHAAN (khusus user corporate) --}}
                @if ($isCorporate)
                    @if ($needsApproval)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-700 px-5 py-4 text-sm">
                            <i class="fa-regular fa-clock mr-1"></i>
                            {{ __('ui.over_limit_notice') }}
                            @if ($limit !== null)
                                <div class="mt-1 text-slate-600">
                                    {{ __('ui.your_limit') }}: <strong>{{ format_price($limit, 'IDR') }}</strong>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-5 py-4 text-sm">
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

            {{-- KOLOM KANAN: KONFIRMASI (1/3 lebar) --}}
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
                    @if ($isOpen)
                        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="room_type" value="{{ $roomType->id }}">
                            <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
                            <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
                            <input type="hidden" name="guests" value="{{ $guests }}">

                            <div class="space-y-1.5">
                                <label for="guest_name" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    {{ __('ui.guest_name') }}
                                </label>
                                <input type="text" id="guest_name" name="guest_name" required maxlength="255"
                                       value="{{ old('guest_name', auth()->user()->name) }}"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                            </div>

                            <button type="submit"
                                    class="w-full px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
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
                           class="block text-center px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-bold transition">
                            {{ __('ui.change_dates') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection