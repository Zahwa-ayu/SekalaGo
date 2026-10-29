@php
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="{{ $box }} md:col-span-3">
        <label class="{{ $label }}"><i class="fa-solid fa-tag text-sagara-blue"></i> {{ __('ui.price_per_night') }} (IDR)</label>
        <input type="number" name="base_price" min="0" required class="{{ $input }}"
               value="{{ old('base_price', $roomType->base_price) }}">
    </div>

    <div class="{{ $box }} md:col-span-1">
        <label class="{{ $label }}"><i class="fa-solid fa-users text-sagara-blue"></i> {{ __('ui.capacity') }}</label>
        <input type="number" name="capacity" min="1" max="20" required class="{{ $input }}"
               value="{{ old('capacity', $roomType->capacity ?? 2) }}">
    </div>

    <div class="{{ $box }} md:col-span-2">
        <label class="{{ $label }}"><i class="fa-solid fa-bed text-sagara-blue"></i> {{ __('ui.total_rooms') }}</label>
        <input type="number" name="total_rooms" min="1" max="1000" required class="{{ $input }}"
               value="{{ old('total_rooms', $roomType->total_rooms ?? 1) }}">
    </div>
</div>

@include('partials.translation-fields', ['model' => $roomType, 'nameLabel' => __('ui.room_name')])