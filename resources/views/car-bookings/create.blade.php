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

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ $vehicle->name }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.confirm_car_booking') }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="sm:flex">
                <div class="sm:w-56 shrink-0 bg-slate-200 aspect-[16/10] sm:aspect-auto">
                    @if ($vehicle->coverUrl())
                        <img src="{{ $vehicle->coverUrl() }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full min-h-32 flex items-center justify-center bg-gradient-to-br from-sagara-navy to-sagara-blue text-white/70 text-4xl">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                    @endif
                </div>

                <div class="p-6 space-y-1">
                    <h2 class="text-xl font-extrabold text-slate-900">{{ $vehicle->name }}</h2>
                    <p class="text-sm text-slate-500">
                        {{ __('ui.vehicle_type_' . $vehicle->type) }} &middot; {{ $vehicle->seats }} {{ __('ui.seats_short') }}
                        &middot; {{ __('ui.transmission_' . $vehicle->transmission) }}
                    </p>
                    <p class="text-sm text-slate-600"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ $vehicle->city }}</p>
                </div>
            </div>

            <table class="w-full text-sm border-t border-slate-100">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-6 py-3 text-slate-500">{{ __('ui.rental_period') }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-900">
                            {{ $pickup->format('d/m/Y') }} - {{ $return->format('d/m/Y') }} ({{ __('ui.for_days', ['count' => $days]) }})
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3 text-slate-500">{{ __('ui.driver_mode') }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-900">{{ $withDriver ? __('ui.with_driver_label') : __('ui.self_drive_label') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3 text-slate-500">{{ __('ui.passengers_count') }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-900">{{ $passengers }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3 text-slate-500">{{ __('ui.price_per_day') }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-900">{{ format_price($vehicle->price_per_day) }}</td>
                    </tr>
                    <tr class="bg-sky-50/60">
                        <td class="px-6 py-3 font-bold text-slate-900">{{ __('ui.rental_total') }}</td>
                        <td class="px-6 py-3 text-lg font-black text-slate-900">{{ format_price($total) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="px-6 py-4 space-y-2 text-xs text-slate-500 border-t border-slate-100">
                @if ($withDriver && $vehicle->driver_fee_per_day !== null)
                    <p><i class="fa-solid fa-user-tie text-sagara-blue"></i>
                        {{ __('ui.driver_fee_estimate_notice', ['amount' => format_price($vehicle->driver_fee_per_day, 'IDR')]) }}</p>
                @endif
                @if ($vehicle->extras_note)
                    <p><i class="fa-solid fa-gas-pump text-sagara-blue"></i> {{ $vehicle->extras_note }}</p>
                @endif
                <p><i class="fa-solid fa-id-card text-sagara-blue"></i> {{ __('ui.bring_id_notice') }}</p>

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

        @if ($isOpen)
            <form method="POST" action="{{ route('car-bookings.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
                @csrf
                <input type="hidden" name="vehicle" value="{{ $vehicle->id }}">
                <input type="hidden" name="pickup_date" value="{{ $pickup->toDateString() }}">
                <input type="hidden" name="return_date" value="{{ $return->toDateString() }}">
                <input type="hidden" name="passengers" value="{{ $passengers }}">
                <input type="hidden" name="with_driver" value="{{ $withDriver ? 1 : 0 }}">

                <label class="block bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-user text-sagara-blue"></i> {{ __('ui.renter_name') }}
                    </span>
                    <input name="guest_name" required maxlength="255"
                           value="{{ old('guest_name', auth()->user()->customer?->full_name ?? auth()->user()->companyUser?->displayName()) }}"
                           class="w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none">
                </label>

                <button class="w-full px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition">
                    @if ($isCorporate)
                        {{ $needsApproval ? __('ui.submit_for_approval') : __('ui.submit_booking') }}
                    @else
                        {{ __('ui.continue_to_payment') }}
                    @endif
                </button>
            </form>
        @else
            <p class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ __('ui.car_unavailable_now') }}</p>
        @endif
    </section>
@endsection