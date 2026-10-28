@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<p>
    <label>{{ __('ui.price_per_night') }} (IDR)<br>
        <input type="number" name="base_price" min="0" value="{{ old('base_price', $roomType->base_price) }}" required>
    </label>
</p>

<p>
    <label>{{ __('ui.capacity') }}<br>
        <input type="number" name="capacity" min="1" max="20" value="{{ old('capacity', $roomType->capacity ?? 2) }}" required>
    </label>
</p>

<p>
    <label>{{ __('ui.total_rooms') }}<br>
        <input type="number" name="total_rooms" min="1" max="1000" value="{{ old('total_rooms', $roomType->total_rooms ?? 1) }}" required>
    </label>
</p>

@include('partials.translation-fields', ['model' => $roomType, 'nameLabel' => __('ui.room_name')])