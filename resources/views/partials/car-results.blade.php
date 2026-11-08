@php
    $vehicles = $search['vehicles'];
    $days     = $search['days'];
    $params   = $search['params'];
    $filters  = $search['filters'];
    $extra    = $extra ?? [];

    $formAction = route($resultsRoute) . '#results';
    $resetUrl   = route($resultsRoute, $params + $extra) . '#results';

    $field = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    $chip  = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold';
@endphp

<div class="space-y-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-md px-5 py-4 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ __('ui.car_results') }}</h2>
        <p class="text-xs sm:text-sm text-slate-500 flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-sagara-blue"></i>
            {{ __('ui.rental_summary', [
                'from' => \Illuminate\Support\Carbon::parse($params['pickup_date'])->format('d/m/Y'),
                'to' => \Illuminate\Support\Carbon::parse($params['return_date'])->format('d/m/Y'),
                'count' => $days,
            ]) }}
        </p>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ $formAction }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-md p-4 grid grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        @foreach ($params + $extra as $key => $value)
            @if (filled($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <select name="type" class="{{ $field }}">
            <option value="">{{ __('ui.all_types') }}</option>
            @foreach (\App\Models\Vehicle::TYPES as $type)
                <option value="{{ $type }}" @selected(($filters['type'] ?? null) === $type)>{{ __('ui.vehicle_type_' . $type) }}</option>
            @endforeach
        </select>

        <input type="number" name="seats" min="1" max="20" value="{{ $filters['seats'] ?? '' }}"
               placeholder="{{ __('ui.min_seats') }}" class="{{ $field }}">

        <select name="transmission" class="{{ $field }}">
            <option value="">{{ __('ui.transmission') }}</option>
            @foreach (\App\Models\Vehicle::TRANSMISSIONS as $item)
                <option value="{{ $item }}" @selected(($filters['transmission'] ?? null) === $item)>{{ __('ui.transmission_' . $item) }}</option>
            @endforeach
        </select>

        <input type="number" name="max_price" min="0" value="{{ $filters['max_price'] ?? '' }}"
               placeholder="{{ __('ui.max_price_per_day') }}" class="{{ $field }}">

        <select name="sort" class="{{ $field }}">
            <option value="price_asc" @selected(($filters['sort'] ?? 'price_asc') === 'price_asc')>{{ __('ui.sort_price_asc') }}</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('ui.sort_price_desc') }}</option>
            <option value="seats_desc" @selected(($filters['sort'] ?? '') === 'seats_desc')>{{ __('ui.sort_seats') }}</option>
        </select>

        <div class="flex gap-2">
            <button class="flex-1 px-4 py-2 rounded-xl bg-sagara-navy text-white text-sm font-bold hover:bg-sagara-blue transition">
                {{ __('ui.apply_filter') }}
            </button>
            <a href="{{ $resetUrl }}" class="px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100 transition" title="{{ __('ui.reset') }}">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>

    {{-- HASIL --}}
    @if ($vehicles->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
            <i class="fa-solid fa-car-side text-3xl mb-2 block"></i>
            {{ __('ui.no_car_results') }}
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($vehicles as $vehicle)
                <a href="{{ route('cars.show', ['vehicle' => $vehicle] + $params) }}"
                   class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition duration-300 flex flex-col">
                    <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                        @if ($vehicle->coverUrl())
                            <img src="{{ $vehicle->coverUrl() }}" alt="{{ $vehicle->name }}" loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-sagara-navy to-sagara-teal flex items-center justify-center text-white/70 text-5xl">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 flex-1 flex flex-col gap-3">
                        <div>
                            <span class="text-[11px] font-extrabold text-sagara-blue uppercase tracking-wider">
                                <i class="fa-solid fa-location-dot"></i> {{ collect([$vehicle->area, $vehicle->city])->filter()->join(', ') }}
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-sagara-blue transition">{{ $vehicle->name }}</h3>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span class="{{ $chip }}"><i class="fa-solid fa-car-side"></i> {{ __('ui.vehicle_type_' . $vehicle->type) }}</span>
                            <span class="{{ $chip }}"><i class="fa-solid fa-chair"></i> {{ $vehicle->seats }}</span>
                            <span class="{{ $chip }}"><i class="fa-solid fa-gears"></i> {{ __('ui.transmission_' . $vehicle->transmission) }}</span>
                            <span class="{{ $chip }}"><i class="fa-solid fa-gas-pump"></i> {{ __('ui.fuel_type_' . $vehicle->fuel_type) }}</span>
                        </div>

                        <div class="text-[11px] font-bold text-emerald-600">
                            <i class="fa-solid fa-id-card-clip"></i> {{ __('ui.driver_mode_' . $vehicle->driver_mode) }}
                        </div>

                        <div class="mt-auto pt-3 border-t border-slate-100">
                            <div class="text-xl font-black text-slate-900">
                                {{ format_price($vehicle->price_per_day) }}<span class="text-xs font-semibold text-slate-400">{{ __('ui.per_day') }}</span>
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ __('ui.rental_total') }}
                                <strong class="text-sagara-blue">{{ format_price($vehicle->price_per_day * $days) }}</strong>
                                ({{ __('ui.for_days', ['count' => $days]) }})
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div>{{ $vehicles->links() }}</div>
    @endif
</div>