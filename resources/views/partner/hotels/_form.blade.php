@php
    $selectedFacilities = old('facilities', $hotel->exists ? $hotel->facilities->pluck('id')->all() : []);
    $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-location-dot text-sagara-blue"></i> {{ __('ui.city') }}</label>
        <input name="city" value="{{ old('city', $hotel->city) }}" required class="{{ $input }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-star text-amber-400"></i> {{ __('ui.star_rating') }}</label>
        <select name="star_rating" class="{{ $input }} cursor-pointer">
            <option value="">-</option>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected(old('star_rating', $hotel->star_rating) == $i)>{{ $i }}</option>
            @endfor
        </select>
    </div>

    <div class="{{ $box }} md:col-span-2">
        <label class="{{ $label }}"><i class="fa-solid fa-map-pin text-sagara-coral"></i> {{ __('ui.address') }}</label>
        <input name="address" value="{{ old('address', $hotel->address) }}" class="{{ $input }}">
    </div>
</div>

@include('partials.translation-fields', ['model' => $hotel, 'nameLabel' => __('ui.hotel_name')])

<fieldset class="border border-slate-200 rounded-2xl p-4">
    <legend class="px-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
        <i class="fa-solid fa-concierge-bell text-sagara-blue"></i> {{ __('ui.facilities') }}
    </legend>
    <div class="flex flex-wrap gap-2 mt-1">
        @foreach ($facilities as $facility)
            <label class="cursor-pointer">
                <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" class="peer sr-only"
                       @checked(in_array($facility->id, $selectedFacilities))>
                <span class="inline-block px-3 py-1.5 text-xs font-semibold rounded-full border border-slate-200 text-slate-600 bg-slate-50 hover:border-sagara-blue transition
                             peer-checked:bg-sagara-blue peer-checked:text-white peer-checked:border-sagara-blue peer-focus-visible:ring-2 peer-focus-visible:ring-sagara-teal">
                    {{ $facility->label }}
                </span>
            </label>
        @endforeach
    </div>
</fieldset>