<div class="nearby-row grid grid-cols-12 gap-2 items-center">
    <select name="nearby[{{ $i }}][type]"
            class="col-span-4 bg-slate-50 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:border-sagara-blue">
        @foreach (\App\Models\NearbyPlace::TYPES as $type)
            <option value="{{ $type }}" @selected(($row['type'] ?? 'other') === $type)>{{ __('ui.nearby_type_' . $type) }}</option>
        @endforeach
    </select>

    <input name="nearby[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" maxlength="120"
           placeholder="{{ __('ui.nearby_name') }}"
           class="col-span-5 bg-slate-50 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:border-sagara-blue">

    <input type="number" step="0.1" min="0" name="nearby[{{ $i }}][distance_km]" value="{{ $row['distance_km'] ?? '' }}"
           placeholder="km"
           class="col-span-2 bg-slate-50 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:border-sagara-blue">

    <button type="button" class="remove-nearby col-span-1 text-red-500 hover:text-red-700 transition" aria-label="Hapus">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>