@php
    $box   = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';

    $selectedFacilities = old('facilities', $hotel->exists ? $hotel->facilities->pluck('id')->all() : []);

    $nearbyRows = old('nearby', $hotel->exists
        ? $hotel->nearbyPlaces->map(fn ($p) => ['type' => $p->type, 'name' => $p->name, 'distance_km' => $p->distance_km])->all()
        : []);
    if (count($nearbyRows) === 0) {
        $nearbyRows = [['type' => 'other', 'name' => '', 'distance_km' => '']];
    }
    $nextIndex = max(array_keys($nearbyRows)) + 1;
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

{{-- LOKASI --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.city') }}</label>
        <select name="city_id" required
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
            <option value="">{{ __('ui.choose_city') }}</option>
            @foreach (\App\Models\City::optionsFor($hotel->city_id ?? null) as $city)
                <option value="{{ $city->id }}" @selected((int) old('city_id', $hotel->city_id ?? 0) === $city->id)>
                    {{ $city->name }}@if ($city->province), {{ $city->province }}@endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-map text-sagara-blue"></i> {{ __('ui.area') }}</label>
        <input name="area" class="{{ $input }}" value="{{ old('area', $hotel->area) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-star text-sagara-blue"></i> {{ __('ui.star_rating') }}</label>
        <select name="star_rating" class="{{ $input }}">
            <option value="">-</option>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected(old('star_rating', $hotel->star_rating) == $i)>{{ $i }}</option>
            @endfor
        </select>
    </div>

    <div class="{{ $box }} md:col-span-3">
        <label class="{{ $label }}"><i class="fa-solid fa-map-pin text-sagara-blue"></i> {{ __('ui.address') }}</label>
        <input name="address" class="{{ $input }}" value="{{ old('address', $hotel->address) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}">{{ __('ui.latitude') }}</label>
        <input type="number" step="any" name="latitude" class="{{ $input }}" placeholder="-8.409518" value="{{ old('latitude', $hotel->latitude) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}">{{ __('ui.longitude') }}</label>
        <input type="number" step="any" name="longitude" class="{{ $input }}" placeholder="115.188919" value="{{ old('longitude', $hotel->longitude) }}">
    </div>

    <p class="text-[11px] text-slate-400 self-center"><i class="fa-solid fa-circle-info"></i> {{ __('ui.coordinates_hint') }}</p>
</div>

{{-- JAM --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-right-to-bracket text-sagara-blue"></i> {{ __('ui.check_in_time') }}</label>
        <input type="time" name="check_in_time" required class="{{ $input }}"
               value="{{ old('check_in_time', $hotel->exists ? $hotel->checkInLabel() : '14:00') }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-right-from-bracket text-sagara-blue"></i> {{ __('ui.check_out_time') }}</label>
        <input type="time" name="check_out_time" required class="{{ $input }}"
               value="{{ old('check_out_time', $hotel->exists ? $hotel->checkOutLabel() : '12:00') }}">
    </div>
</div>

@include('partials.translation-fields', ['model' => $hotel, 'nameLabel' => __('ui.hotel_name')])

{{-- FASILITAS HOTEL --}}
<div>
    <div class="{{ $label }}"><i class="fa-solid fa-hotel text-sagara-blue"></i> {{ __('ui.hotel_facilities') }}</div>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach ($facilities as $facility)
            <label class="flex items-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 cursor-pointer hover:border-sagara-blue transition">
                <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" class="accent-sagara-blue"
                       @checked(in_array($facility->id, $selectedFacilities))>
                <i class="fa-solid {{ $facility->icon() }} text-sagara-blue w-4 text-center"></i>
                {{ $facility->label }}
            </label>
        @endforeach
    </div>
</div>

{{-- TEMPAT TERDEKAT --}}
<div>
    <div class="{{ $label }}"><i class="fa-solid fa-compass text-sagara-blue"></i> {{ __('ui.nearby_places') }}</div>

    <div id="nearby-list" class="space-y-2" data-next-index="{{ $nextIndex }}">
        @foreach ($nearbyRows as $i => $row)
            @include('partner.hotels._nearby-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>

    <button type="button" id="add-nearby"
            class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-100 text-sagara-blue text-xs font-bold hover:bg-sky-200 transition">
        <i class="fa-solid fa-plus"></i> {{ __('ui.add_nearby') }}
    </button>

    <template id="nearby-template">
        @include('partner.hotels._nearby-row', ['i' => '__INDEX__', 'row' => []])
    </template>
</div>

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('nearby-list');
        const template = document.getElementById('nearby-template').innerHTML;
        let index = parseInt(list.dataset.nextIndex, 10);

        document.getElementById('add-nearby').addEventListener('click', () => {
            if (list.querySelectorAll('.nearby-row').length >= 10) return;
            list.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index++));
        });

        list.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-nearby');
            if (button) button.closest('.nearby-row').remove();
        });
    })();
</script>
@endpush