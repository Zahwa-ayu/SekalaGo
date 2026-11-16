@php
    $user    = auth()->user();
    $isCar   = $type === 'car';
    $corp    = $isCorporate ?? false;
    $self    = $selfDrive ?? false;
    $name    = $user->customer?->full_name ?? $user->companyUser?->full_name ?? '';
    $input   = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
    $label   = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
@endphp

<div class="space-y-6">
    {{-- PEMESAN --}}
    <div class="space-y-3">
        <div>
            <h3 class="font-extrabold text-slate-900 text-sm">{{ __('ui.booker_data') }}</h3>
            <p class="text-xs text-slate-500">{{ __('ui.booker_data_hint') }}</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">{{ __('ui.contact_name') }}</label>
                <input name="contact_name" id="contact_name" required maxlength="255" value="{{ old('contact_name', $name) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">{{ __('ui.contact_phone') }}</label>
                <input name="contact_phone" required inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx"
                       value="{{ old('contact_phone', $user->phone_number) }}" class="{{ $input }}">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">{{ __('ui.contact_email') }}</label>
                <input type="email" name="contact_email" required maxlength="255" value="{{ old('contact_email', $user->email) }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    {{-- TAMU / PENYEWA --}}
    <div class="space-y-3">
        <h3 class="font-extrabold text-slate-900 text-sm">{{ $isCar ? __('ui.renter_data') : __('ui.guest_data') }}</h3>

        <div>
            <div class="flex items-center justify-between">
                <label class="{{ $label }}">{{ $isCar ? __('ui.renter_name_id') : __('ui.guest_name_id') }}</label>
                <button type="button" onclick="document.getElementById('guest_name').value = document.getElementById('contact_name').value"
                        class="text-[11px] font-bold text-sky-600 hover:underline mb-1">{{ __('ui.same_as_booker') }}</button>
            </div>
            <input name="guest_name" id="guest_name" required maxlength="255" value="{{ old('guest_name', $name) }}" class="{{ $input }}">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">{{ $isCar ? __('ui.pickup_time') : __('ui.arrival_time') }}@if ($isCar) *@endif</label>
                <input type="time" name="arrival_time" @required($isCar) value="{{ old('arrival_time') }}" class="{{ $input }}">
                @unless ($isCar)
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.arrival_time_hint') }}</p>
                @endunless
            </div>

            @if ($isCar)
                <div>
                    <label class="{{ $label }}">{{ __('ui.destination') }}</label>
                    <input name="destination" maxlength="255" value="{{ old('destination') }}" placeholder="{{ __('ui.destination_hint') }}" class="{{ $input }}">
                </div>
            @endif
        </div>

        <div>
            <label class="{{ $label }}">{{ __('ui.special_requests') }}</label>
            <textarea name="special_requests" rows="2" maxlength="500" placeholder="{{ __('ui.special_requests_hint') }}" class="{{ $input }}">{{ old('special_requests') }}</textarea>
        </div>

        @if ($isCar && $self)
            <label class="flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-900">
                <input type="checkbox" name="license_confirmed" value="1" required @checked(old('license_confirmed')) class="mt-0.5 rounded border-amber-300 text-sky-500">
                <span>{{ __('ui.license_confirm') }}</span>
            </label>
        @endif
    </div>

    {{-- CORPORATE --}}
    @if ($corp)
        <div class="space-y-3">
            <h3 class="font-extrabold text-slate-900 text-sm">{{ __('ui.business_trip_data') }}</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.trip_purpose') }} *</label>
                    <input name="trip_purpose" list="trip-purposes" required maxlength="100" value="{{ old('trip_purpose') }}" class="{{ $input }}">
                    <datalist id="trip-purposes">
                        @foreach (['trip_purpose_client', 'trip_purpose_project', 'trip_purpose_training', 'trip_purpose_event', 'trip_purpose_other'] as $key)
                            <option value="{{ __('ui.' . $key) }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.cost_center') }}</label>
                    <input name="cost_center" maxlength="60" value="{{ old('cost_center') }}" class="{{ $input }}">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.cost_center_hint') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>