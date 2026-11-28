@php
    $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';

    $selectedFeatures = old('features', $vehicle->features ?? []);
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

{{-- NAMA DAN DESKRIPSI (per bahasa) --}}
<div class="{{ $card }} space-y-5">
    @include('partials.translation-fields', ['model' => $vehicle, 'nameLabel' => __('ui.vehicle_name')])
</div>

{{-- SPESIFIKASI --}}
<div class="{{ $card }} space-y-4">
    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-car-side text-sagara-blue"></i> {{ __('ui.vehicle_specs') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $label }}">{{ __('ui.vehicle_type') }}</label>
            <select name="type" class="{{ $input }}">
                @foreach (\App\Models\Vehicle::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $vehicle->type ?? 'mpv') === $type)>{{ __('ui.vehicle_type_' . $type) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.transmission') }}</label>
            <select name="transmission" class="{{ $input }}">
                @foreach (\App\Models\Vehicle::TRANSMISSIONS as $item)
                    <option value="{{ $item }}" @selected(old('transmission', $vehicle->transmission ?? 'manual') === $item)>{{ __('ui.transmission_' . $item) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.fuel_type') }}</label>
            <select name="fuel_type" class="{{ $input }}">
                @foreach (\App\Models\Vehicle::FUEL_TYPES as $item)
                    <option value="{{ $item }}" @selected(old('fuel_type', $vehicle->fuel_type ?? 'petrol') === $item)>{{ __('ui.fuel_type_' . $item) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.seats') }}</label>
            <input type="number" name="seats" min="1" max="20" required class="{{ $input }}" value="{{ old('seats', $vehicle->seats ?? 4) }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.luggage_capacity') }}</label>
            <input type="number" name="luggage_capacity" min="0" max="20" class="{{ $input }}" value="{{ old('luggage_capacity', $vehicle->luggage_capacity) }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.model_year') }}</label>
            <input type="number" name="model_year" min="1990" max="{{ now()->year + 1 }}" class="{{ $input }}" value="{{ old('model_year', $vehicle->model_year) }}">
        </div>
    </div>
</div>

{{-- LOKASI --}}
<div class="{{ $card }} space-y-4">
    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.pickup_location') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">{{ __('ui.city') }}</label>
            <select name="city_id" required class="{{ $input }}">
                <option value="">{{ __('ui.choose_city') }}</option>
                @foreach (\App\Models\City::optionsFor($vehicle->city_id ?? null) as $city)
                    <option value="{{ $city->id }}" @selected((int) old('city_id', $vehicle->city_id ?? 0) === $city->id)>
                        {{ $city->name }}@if ($city->province), {{ $city->province }}@endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.area') }}</label>
            <input name="area" class="{{ $input }}" value="{{ old('area', $vehicle->area) }}">
        </div>

        <div class="md:col-span-2">
            <label class="{{ $label }}">{{ __('ui.address') }}</label>
            <input name="address" class="{{ $input }}" value="{{ old('address', $vehicle->address) }}">
        </div>
    </div>
</div>

{{-- KETENTUAN SEWA --}}
<div class="{{ $card }} space-y-4">
    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-file-contract text-sagara-blue"></i> {{ __('ui.rental_terms') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $label }}">{{ __('ui.price_per_day') }}</label>
            <input type="number" name="price_per_day" min="0" required class="{{ $input }}" value="{{ old('price_per_day', $vehicle->price_per_day) }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.units_available') }}</label>
            <input type="number" name="units" min="1" max="100" required class="{{ $input }}" value="{{ old('units', $vehicle->units ?? 1) }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.min_days') }}</label>
            <input type="number" name="min_days" min="1" max="30" required class="{{ $input }}" value="{{ old('min_days', $vehicle->min_days ?? 1) }}">
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.driver_mode') }}</label>
            <select name="driver_mode" class="{{ $input }}">
                @foreach (\App\Models\Vehicle::DRIVER_MODES as $mode)
                    <option value="{{ $mode }}" @selected(old('driver_mode', $vehicle->driver_mode ?? 'both') === $mode)>{{ __('ui.driver_mode_' . $mode) }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="{{ $label }}">{{ __('ui.driver_fee') }}</label>
            <input type="number" name="driver_fee_per_day" min="0" class="{{ $input }}" value="{{ old('driver_fee_per_day', $vehicle->driver_fee_per_day) }}">
            <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.driver_fee_hint') }}</p>
        </div>

        <div class="md:col-span-3">
            <label class="{{ $label }}">{{ __('ui.extras_note') }}</label>
            <input name="extras_note" maxlength="500" class="{{ $input }}" value="{{ old('extras_note', $vehicle->extras_note) }}">
        </div>
    </div>

    {{-- JAM PENGEMBALIAN DAN KETERLAMBATAN --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label class="{{ $label }}">{{ __('ui.return_time') }}</label>
            <input type="time" name="return_time" required class="{{ $input }}"
                   value="{{ old('return_time', substr((string) ($vehicle->return_time ?? '12:00'), 0, 5)) }}">
        </div>
        <div>
            <label class="{{ $label }}">{{ __('ui.late_grace_minutes') }}</label>
            <input type="number" name="late_grace_minutes" min="0" max="240" required class="{{ $input }}"
                   value="{{ old('late_grace_minutes', $vehicle->late_grace_minutes ?? 60) }}">
        </div>
        <div>
            <label class="{{ $label }}">{{ __('ui.late_fee_percent') }}</label>
            <input type="number" name="late_fee_percent" min="0" max="100" required class="{{ $input }}"
                   value="{{ old('late_fee_percent', $vehicle->late_fee_percent ?? 10) }}">
        </div>
    </div>
</div>

{{-- FITUR --}}
<div class="{{ $card }} space-y-4">
    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-star text-sagara-blue"></i> {{ __('ui.vehicle_features') }}</h2>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach (\App\Models\Vehicle::FEATURE_ICONS as $code => $icon)
            <label class="flex items-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 cursor-pointer hover:border-sagara-blue transition">
                <input type="checkbox" name="features[]" value="{{ $code }}" class="accent-sagara-blue" @checked(in_array($code, $selectedFeatures))>
                <i class="fa-solid {{ $icon }} text-sagara-blue w-4 text-center"></i>
                {{ __('ui.feature_' . $code) }}
            </label>
        @endforeach
    </div>
</div>