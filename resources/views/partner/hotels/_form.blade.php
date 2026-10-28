@php
    $selectedFacilities = old('facilities', $hotel->exists ? $hotel->facilities->pluck('id')->all() : []);
@endphp

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<p>
    <label>{{ __('ui.city') }}<br>
        <input name="city" value="{{ old('city', $hotel->city) }}" required>
    </label>
</p>

<p>
    <label>{{ __('ui.address') }}<br>
        <input name="address" value="{{ old('address', $hotel->address) }}">
    </label>
</p>

<p>
    <label>{{ __('ui.star_rating') }}<br>
        <select name="star_rating">
            <option value="">-</option>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected(old('star_rating', $hotel->star_rating) == $i)>{{ $i }}</option>
            @endfor
        </select>
    </label>
</p>

@include('partials.translation-fields', ['model' => $hotel, 'nameLabel' => __('ui.hotel_name')])

<fieldset>
    <legend>{{ __('ui.facilities') }}</legend>
    @foreach ($facilities as $facility)
        <label>
            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                   @checked(in_array($facility->id, $selectedFacilities))>
            {{ $facility->label }}
        </label>
    @endforeach
</fieldset>