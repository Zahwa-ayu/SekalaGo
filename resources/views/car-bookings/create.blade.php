@extends('layouts.app')

@section('content')
    @php
        $backUrl = route('cars.show', [
            'vehicle' => $vehicle->id,
            'pickup_date' => $pickup->toDateString(),
            'return_date' => $return->toDateString(),
            'passengers' => $passengers,
            'driver' => $withDriver ? 'with_driver' : 'self_drive',
        ]);
    @endphp

    {{-- BANNER BIRU (senada dengan hero Beranda dan halaman pesanan hotel) --}}
    <div class="bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 pt-8 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-white hover:underline">
                <i class="fa-solid fa-arrow-left"></i> {{ $vehicle->name }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white drop-shadow-sm">{{ __('ui.confirm_car_booking') }}</h1>
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
                    <form method="POST" action="{{ route('car-bookings.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="vehicle" value="{{ $vehicle->id }}">
                        <input type="hidden" name="pickup_date" value="{{ $pickup->toDateString() }}">
                        <input type="hidden" name="return_date" value="{{ $return->toDateString() }}">
                        <input type="hidden" name="passengers" value="{{ $passengers }}">
                        <input type="hidden" name="with_driver" value="{{ $withDriver ? 1 : 0 }}">

                        @include('partials.booking-details-fields', ['type' => 'car', 'isCorporate' => $isCorporate, 'selfDrive' => ! $withDriver])

                        {{-- KEBIJAKAN DENDA PEMBATALAN (sesuai mitra) --}}
                        <p class="text-xs text-slate-500">{{ __('ui.cancel_fee_policy', ['percent' => $vehicle->partner->lateCancelFeePercent()]) }}</p>

                        <button type="submit"
                                class="w-full px-5 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition">
                            @if ($isCorporate)
                                {{ $needsApproval ? __('ui.submit_for_approval') : __('ui.submit_booking') }}
                            @else
                                <i class="fa-solid fa-credit-card mr-1"></i> {{ __('ui.continue_to_payment') }}
                            @endif
                        </button>
                    </form>
                @else
                    <p class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">{{ __('ui.car_unavailable_now') }}</p>
                    <a href="{{ $backUrl }}"
                       class="block text-center px-5 py-3 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-700 text-sm font-bold transition">
                        {{ $vehicle->name }}
                    </a>
                @endif
            </div>

            {{-- KOLOM KANAN: RINGKASAN SEWA (1/3 lebar), menempel saat halaman di-scroll --}}
            <div class="order-1 lg:order-2 lg:sticky lg:top-24 bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden">

                {{-- Foto kendaraan --}}
                <div class="bg-slate-200 aspect-[16/10]">
                    @if ($vehicle->coverUrl())
                        <img src="{{ $vehicle->coverUrl() }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-sky-700 to-sky-500 text-white/70 text-4xl">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                    @endif
                </div>

                <div class="p-6 space-y-5">
                    <div class="space-y-1">
                        <h2 class="text-lg font-extrabold text-slate-900">{{ $vehicle->name }}</h2>
                        <p class="text-sm text-slate-500">
                            {{ __('ui.vehicle_type_' . $vehicle->type) }} &middot; {{ $vehicle->seats }} {{ __('ui.seats_short') }}
                            &middot; {{ __('ui.transmission_' . $vehicle->transmission) }}
                        </p>
                        <p class="text-sm text-slate-600"><i class="fa-solid fa-location-dot text-sky-500"></i> {{ $vehicle->city }}</p>
                    </div>

                    {{-- Rincian sewa --}}
                    <dl class="text-sm divide-y divide-slate-100 border-y border-slate-100">
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-slate-500 shrink-0">{{ __('ui.rental_period') }}</dt>
                            <dd class="font-semibold text-slate-900 text-right">
                                {{ $pickup->format('d/m/Y') }} - {{ $return->format('d/m/Y') }}
                                <span class="block text-xs font-medium text-slate-500">{{ __('ui.for_days', ['count' => $days]) }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-slate-500 shrink-0">{{ __('ui.driver_mode') }}</dt>
                            <dd class="font-semibold text-slate-900 text-right">{{ $withDriver ? __('ui.with_driver_label') : __('ui.self_drive_label') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-slate-500 shrink-0">{{ __('ui.passengers_count') }}</dt>
                            <dd class="font-semibold text-slate-900 text-right">{{ $passengers }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-slate-500 shrink-0">{{ __('ui.price_per_day') }}</dt>
                            <dd class="font-semibold text-slate-900 text-right">{{ format_price($vehicle->price_per_day) }}</dd>
                        </div>
                    </dl>

                    {{-- Total --}}
                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-sky-50 border border-sky-100 px-4 py-3">
                        <span class="font-bold text-slate-900 text-sm">{{ __('ui.rental_total') }}</span>
                        <span class="text-xl font-black text-slate-900">{{ format_price($total) }}</span>
                    </div>

                    {{-- Catatan --}}
                    <div class="space-y-2 text-xs text-slate-500">
                        @if ($withDriver && $vehicle->driver_fee_per_day !== null)
                            <p><i class="fa-solid fa-user-tie text-sky-500"></i>
                                {{ __('ui.driver_fee_estimate_notice', ['amount' => format_price($vehicle->driver_fee_per_day, 'IDR')]) }}</p>
                        @endif
                        @if ($vehicle->extras_note)
                            <p><i class="fa-solid fa-gas-pump text-sky-500"></i> {{ $vehicle->extras_note }}</p>
                        @endif
                        <p><i class="fa-solid fa-clock text-sky-500"></i>
                            {{ __('ui.return_policy_note', [
                                'time'    => substr((string) $vehicle->return_time, 0, 5),
                                'grace'   => $vehicle->late_grace_minutes,
                                'percent' => $vehicle->late_fee_percent,
                            ]) }}</p>
                        <p><i class="fa-solid fa-id-card text-sky-500"></i> {{ __('ui.bring_id_notice') }}</p>

                        @if ($isCorporate)
                            <p>{{ __('ui.paid_by_deposit') }}</p>
                            <p class="text-sm text-slate-700">
                                {{ __('ui.your_limit') }}: <strong>{{ format_price($limit, 'IDR') }}</strong>
                                @if ($policy['budget'] !== null)
                                    <br>{{ __('ui.monthly_budget') }}: <strong>{{ format_price($policy['budget'], 'IDR') }}</strong>
                                    ({{ __('ui.used_this_month') }}: {{ format_price($policy['spent'], 'IDR') }},
                                    {{ __('ui.remaining') }}: {{ format_price(max(0, $policy['budget'] - $policy['spent']), 'IDR') }})
                                @endif
                            </p>
                            @if ($needsApproval)
                                <p class="text-sm font-semibold text-amber-600">
                                    @if ($policy['over_limit']) {{ __('ui.over_limit_notice', ['limit' => format_price($limit, 'IDR')]) }} @endif
                                    @if ($policy['over_budget']) {{ __('ui.over_budget_notice') }} @endif
                                </p>
                            @elseif ($isFinance && ($policy['over_limit'] || $policy['over_budget']))
                                <p class="text-sm text-slate-700">{{ __('ui.finance_exempt_notice') }}</p>
                            @else
                                <p class="text-sm text-slate-700">{{ __('ui.within_limit_notice') }}</p>
                            @endif
                        @else
                            <p>{{ __('ui.charged_in_idr', ['amount' => format_price($total, 'IDR')]) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection