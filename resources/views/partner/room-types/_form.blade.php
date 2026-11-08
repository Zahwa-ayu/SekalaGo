@php
    $box   = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';

    $selectedFacilities = old('facilities', $roomType->exists ? $roomType->facilities->pluck('id')->all() : []);
    $smoking = (string) old('smoking_allowed', $roomType->exists ? (int) $roomType->smoking_allowed : 0);

    // Harga mentah (angka biasa) dari old() atau database
    $priceRaw = old('base_price', $roomType->base_price);
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="{{ $box }} md:col-span-3">
        <label class="{{ $label }}"><i class="fa-solid fa-tag text-sagara-blue"></i> {{ __('ui.price_per_night') }} (IDR)</label>

        {{-- Input tampilan: ada titiknya, tanpa name (tidak ikut terkirim) --}}
        <input type="text" id="base_price_display" inputmode="numeric" autocomplete="off" required
               class="{{ $input }}"
               value="{{ $priceRaw !== null && $priceRaw !== '' ? number_format((float) $priceRaw, 0, ',', '.') : '' }}">

        {{-- Input asli yang dikirim ke server: angka mentah tanpa titik --}}
        <input type="hidden" name="base_price" id="base_price" value="{{ $priceRaw }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-users text-sagara-blue"></i> {{ __('ui.capacity') }}</label>
        <input type="number" name="capacity" min="1" max="20" required class="{{ $input }}"
               value="{{ old('capacity', $roomType->capacity ?? 2) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-bed text-sagara-blue"></i> {{ __('ui.bed_type') }}</label>
        <select name="bed_type" class="{{ $input }}">
            @foreach (\App\Models\RoomType::BED_TYPES as $type)
                <option value="{{ $type }}" @selected(old('bed_type', $roomType->bed_type ?? 'double') === $type)>{{ __('ui.bed_type_' . $type) }}</option>
            @endforeach
        </select>
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-hashtag text-sagara-blue"></i> {{ __('ui.bed_count') }}</label>
        <input type="number" name="bed_count" min="1" max="6" required class="{{ $input }}"
               value="{{ old('bed_count', $roomType->bed_count ?? 1) }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-utensils text-sagara-blue"></i> {{ __('ui.meal_plan') }}</label>
        <select name="meal_plan" class="{{ $input }}">
            @foreach (\App\Models\RoomType::MEAL_PLANS as $plan)
                <option value="{{ $plan }}" @selected(old('meal_plan', $roomType->meal_plan ?? 'room_only') === $plan)>{{ __('ui.meal_plan_' . $plan) }}</option>
            @endforeach
        </select>
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-smoking text-sagara-blue"></i> {{ __('ui.smoking_label') }}</label>
        <select name="smoking_allowed" class="{{ $input }}">
            <option value="0" @selected($smoking === '0')>{{ __('ui.smoking_no') }}</option>
            <option value="1" @selected($smoking === '1')>{{ __('ui.smoking_yes') }}</option>
        </select>
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-door-open text-sagara-blue"></i> {{ __('ui.total_rooms') }}</label>
        <input type="number" name="total_rooms" min="1" max="1000" required class="{{ $input }}"
               value="{{ old('total_rooms', $roomType->total_rooms ?? 1) }}">
    </div>
</div>

@include('partials.translation-fields', ['model' => $roomType, 'nameLabel' => __('ui.room_name')])

{{-- FASILITAS KAMAR --}}
<div>
    <div class="{{ $label }}"><i class="fa-solid fa-bed text-sagara-blue"></i> {{ __('ui.room_facilities') }}</div>
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

{{-- Format titik ribuan untuk input harga (hanya tampilan; nilai asli di input hidden) --}}
<script>
    (function () {
        const display = document.getElementById('base_price_display');
        const hidden  = document.getElementById('base_price');
        if (!display || !hidden) return;

        display.addEventListener('input', function () {
            const digits = display.value.replace(/\D/g, '');   // buang semua selain angka
            hidden.value = digits;
            display.value = digits ? new Intl.NumberFormat('id-ID').format(digits) : '';
        });
    })();
</script>