@php
    $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
    $selectedFacilities = old('facilities', $hotel->exists ? $hotel->facilities->pluck('id')->all() : []);
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="{{ $box }} md:col-span-2">
        <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.city') }}</label>
        <input name="city" required class="{{ $input }}" value="{{ old('city', $hotel->city) }}">
    </div>

    <div class="{{ $box }} md:col-span-1">
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
</div>

@include('partials.translation-fields', ['model' => $hotel, 'nameLabel' => __('ui.hotel_name')])

<div>
    <div class="{{ $label }}"><i class="fa-solid fa-wifi text-sagara-blue"></i> {{ __('ui.facilities') }}</div>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach ($facilities as $facility)
            <label class="flex items-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 cursor-pointer hover:border-sagara-blue transition">
                <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" class="accent-sagara-blue"
                       @checked(in_array($facility->id, $selectedFacilities))>
                {{ $facility->label }}
            </label>
        @endforeach
    </div>
</div>