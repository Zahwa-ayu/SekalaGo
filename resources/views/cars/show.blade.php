@extends('layouts.app')

@section('content')
    @php
        $photos   = $vehicle->photos;
        $chip     = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold';
        $card     = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $input    = 'w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sagara-blue';
        $mapQuery = rawurlencode(collect([$vehicle->address, $vehicle->area, $vehicle->city])->filter()->join(', '));
        $backUrl  = $params
            ? route('cars.search', array_merge($params, ['city' => $params['city'] ?? $vehicle->city]))
            : route('cars.home');

    $previous = url()->previous();
    $samePage = strtok($previous, '?') === strtok(url()->current(), '?');
    $backUrl  = (! $samePage && str_starts_with($previous, url('/')))
        ? $previous
        : ($params
            ? route('cars.home', array_merge($params, ['city' => $params['city'] ?? $vehicle->city]))
            : route('cars.home'));
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.car_results') }}
        </a>

        {{-- items-start: supaya kolom kanan tidak ditarik setinggi kolom kiri (penyebab sticky menimpa kartu lain) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:items-start">

            {{-- ============ KOLOM KIRI ============ --}}
            <div class="lg:col-span-2 min-w-0 space-y-6">

                {{-- GALERI: foto utama + deretan thumbnail di bawahnya --}}
                <div class="space-y-3">
                    <div class="w-full rounded-3xl overflow-hidden bg-slate-200 aspect-[16/10]">
                        @if ($photos->isNotEmpty())
                            <img id="vehicle-main-photo" src="{{ $photos->first()->url() }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-sagara-navy to-sagara-blue text-white/70 text-6xl">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                        @endif
                    </div>

                    @if ($photos->count() > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach ($photos->take(6) as $photo)
                                <button type="button"
                                        onclick="document.getElementById('vehicle-main-photo').src='{{ $photo->url() }}'"
                                        class="rounded-xl overflow-hidden aspect-[4/3] bg-slate-200 hover:ring-2 hover:ring-sagara-teal transition">
                                    <img src="{{ $photo->url() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- JUDUL + CHIP --}}
                <div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 break-words">{{ $vehicle->name }}</h1>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="{{ $chip }}"><i class="fa-solid fa-car-side text-sagara-blue"></i> {{ __('ui.vehicle_type_' . $vehicle->type) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-chair text-sagara-blue"></i> {{ $vehicle->seats }} {{ __('ui.seats_short') }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-gears text-sagara-blue"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                        <span class="{{ $chip }}"><i class="fa-solid fa-gas-pump text-sagara-blue"></i> {{ __('ui.fuel_type_' . $vehicle->fuel_type) }}</span>
                        @if ($vehicle->luggage_capacity !== null)
                            <span class="{{ $chip }}"><i class="fa-solid fa-suitcase-rolling text-sagara-blue"></i> {{ $vehicle->luggage_capacity }}</span>
                        @endif
                        @if ($vehicle->model_year)
                            <span class="{{ $chip }}"><i class="fa-solid fa-calendar text-sagara-blue"></i> {{ $vehicle->model_year }}</span>
                        @endif
                    </div>
                </div>

                @if ($vehicle->description)
                    <div class="{{ $card }}">
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line break-words">{{ $vehicle->description }}</p>
                    </div>
                @endif

                @if (! empty($vehicle->features))
                    <div class="{{ $card }}">
                        <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.vehicle_features') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($vehicle->features as $feature)
                                <span class="{{ $chip }}">
                                    <i class="fa-solid {{ \App\Models\Vehicle::FEATURE_ICONS[$feature] ?? 'fa-circle-check' }} text-sagara-blue"></i>
                                    {{ __('ui.feature_' . $feature) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- SUPIR DAN BIAYA TAMBAHAN --}}
                <div class="{{ $card }} space-y-3 text-sm text-slate-700">
                    <h2 class="font-extrabold text-slate-900">{{ __('ui.driver_and_extras') }}</h2>

                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-id-card-clip text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                        <span>{{ __('ui.driver_mode_' . $vehicle->driver_mode) }}</span>
                    </div>

                    @if ($vehicle->offersDriver() && $vehicle->driver_fee_per_day !== null)
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-user-tie text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                            <span>{{ __('ui.driver_costs_note', ['amount' => format_price($vehicle->driver_fee_per_day, 'IDR')]) }}</span>
                        </div>
                    @endif

                    @if ($vehicle->extras_note)
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-gas-pump text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                            <span>{{ $vehicle->extras_note }}</span>
                        </div>
                    @endif

                    <p class="text-[11px] text-slate-400"><i class="fa-solid fa-circle-info"></i> {{ __('ui.extras_paid_to_vendor') }}</p>
                </div>
            </div>

            {{-- ============ KOLOM KANAN ============
                 Seluruh kolom yang sticky (bukan hanya kartu pertama),
                 jadi kartu lokasi ikut bergerak dan tidak ditimpa. --}}
            <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
                <div class="{{ $card }} space-y-4">
                    <div class="text-2xl font-black text-slate-900">
                        {{ format_price($vehicle->price_per_day) }}<span class="text-sm font-semibold text-slate-400">{{ __('ui.per_day') }}</span>
                    </div>

                    @if ($params)
                        <div class="text-sm text-slate-600">
                            {{ __('ui.rental_summary', [
                                'from' => \Illuminate\Support\Carbon::parse($params['pickup_date'])->format('d/m/Y'),
                                'to' => \Illuminate\Support\Carbon::parse($params['return_date'])->format('d/m/Y'),
                                'count' => $days,
                            ]) }}
                        </div>

                        <div class="text-sm text-slate-600">
                            {{ __('ui.rental_total') }}:
                            <strong class="text-slate-900">{{ format_price($vehicle->price_per_day * $days) }}</strong>
                            ({{ __('ui.for_days', ['count' => $days]) }})
                        </div>

                        @if ($days < $vehicle->min_days)
                            <div class="text-xs font-bold text-red-500">{{ __('ui.min_days_notice', ['count' => $vehicle->min_days]) }}</div>
                        @endif

                        <div class="text-sm font-bold {{ $available ? 'text-emerald-600' : 'text-red-500' }}">
                            <i class="fa-solid {{ $available ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            {{ $available ? __('ui.car_available') : __('ui.car_unavailable') }}
                        </div>

                        @if ($available && $freeUnits !== null && $freeUnits <= 2)
                            <div class="text-xs font-bold text-amber-600">{{ __('ui.units_left', ['count' => $freeUnits]) }}</div>
                        @endif
                    @else
                        <p class="text-sm text-slate-500">{{ __('ui.pick_car_dates_hint') }}</p>
                    @endif

                    {{-- Ubah tanggal, penumpang, dan opsi supir --}}
                    <form method="GET" action="{{ route('cars.show', $vehicle) }}" class="grid grid-cols-2 gap-2">
                        <input type="date" name="pickup_date" required min="{{ today()->toDateString() }}"
                               value="{{ $params['pickup_date'] ?? today()->addDay()->toDateString() }}"
                               class="{{ $input }}">
                        <input type="date" name="return_date" required min="{{ today()->addDay()->toDateString() }}"
                               value="{{ $params['return_date'] ?? today()->addDays(3)->toDateString() }}"
                               class="{{ $input }}">

                        <input type="number" name="passengers" min="1" max="{{ $vehicle->seats }}" value="{{ $passengers }}"
                               title="{{ __('ui.passengers_count') }}" placeholder="{{ __('ui.passengers_count') }}"
                               class="{{ $input }}">

                        @if ($vehicle->offersDriver() && $vehicle->offersSelfDrive())
                            <select name="driver" class="{{ $input }}">
                                <option value="with_driver" @selected(($driver ?? 'with_driver') === 'with_driver')>{{ __('ui.driver_option_with') }}</option>
                                <option value="self_drive" @selected(($driver ?? '') === 'self_drive')>{{ __('ui.driver_option_self') }}</option>
                            </select>
                        @else
                            <div class="min-w-0 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 flex items-center">
                                {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}
                            </div>
                        @endif

                        <button class="col-span-2 px-4 py-2 rounded-xl bg-sky-100 text-sagara-blue text-xs font-bold hover:bg-sky-200 transition">
                            {{ __('ui.check_availability') }}
                        </button>
                    </form>

                    @if ($errors->any())
                        <ul class="text-xs text-red-600 list-disc list-inside">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    @endif

                    {{-- Tombol sewa --}}
                    @if ($params && $available)
                        <a href="{{ route('car-bookings.create', [
                            'vehicle'     => $vehicle->id,
                            'pickup_date' => $params['pickup_date'],
                            'return_date' => $params['return_date'],
                            'passengers'  => $passengers,
                            'with_driver' => $driver === 'with_driver' ? 1 : 0,
                        ]) }}"
                           class="block w-full text-center px-6 py-3 rounded-xl bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white text-sm font-bold shadow-lg transition">
                            {{ __('ui.book_car') }}
                        </a>
                    @else
                        <button disabled class="w-full px-6 py-3 rounded-xl bg-slate-200 text-slate-400 text-sm font-bold cursor-not-allowed">
                            {{ __('ui.book_car') }}
                        </button>
                    @endif
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-extrabold text-slate-900 mb-3">{{ __('ui.pickup_location') }}</h2>
                    <p class="text-sm text-slate-700 flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-sagara-blue w-5 mt-0.5 shrink-0"></i>
                        <span class="break-words">{{ collect([$vehicle->address, $vehicle->area, $vehicle->city])->filter()->join(', ') }}</span>
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:underline">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('ui.view_map') }}
                    </a>
                </div>
            </aside>
        </div>
    </section>
@endsection