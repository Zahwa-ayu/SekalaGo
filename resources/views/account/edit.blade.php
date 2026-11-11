@extends('account.layout')

@section('account')
    @php
        $type  = $user->user_type;
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    @endphp

    <form method="POST" action="{{ route('account.update') }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        {{-- PROFIL --}}
        @if (in_array($type, ['customer', 'admin', 'corporate'], true))
            <div class="space-y-4">
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.account_profile') }}</h2>

                <div>
                    <label class="{{ $label }}">{{ __('ui.full_name') }}</label>
                    <input name="full_name" required maxlength="255" class="{{ $input }}"
                           value="{{ old('full_name', $profile?->full_name) }}">
                </div>

                @if ($type === 'customer')
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $label }}">{{ __('ui.birth_date') }}</label>
                            <input type="date" name="birth_date" max="{{ today()->subDay()->toDateString() }}" class="{{ $input }}"
                                   value="{{ old('birth_date', substr((string) $profile?->birth_date, 0, 10)) }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ __('ui.gender') }}</label>
                            <select name="gender" class="{{ $input }}">
                                <option value="">-</option>
                                <option value="male" @selected(old('gender', $profile?->gender) === 'male')>{{ __('ui.gender_male') }}</option>
                                <option value="female" @selected(old('gender', $profile?->gender) === 'female')>{{ __('ui.gender_female') }}</option>
                            </select>
                        </div>
                    </div>
                @endif

                @if ($type === 'admin')
                    <div>
                        <label class="{{ $label }}">{{ __('ui.position') }}</label>
                        <input name="position" maxlength="100" class="{{ $input }}" value="{{ old('position', $profile?->position) }}">
                    </div>
                @endif

                @if ($type === 'corporate')
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-circle-info text-sky-500"></i> {{ __('ui.account_corporate_hint') }}
                    </p>
                @endif
            </div>
        @else
            <div class="rounded-2xl bg-sky-50 border border-sky-100 px-5 py-4 text-sm text-sky-800 space-y-1">
                <div>{{ __('ui.business_profile_hint') }}</div>
                <a href="{{ route('partner.profile.edit') }}" class="font-bold hover:underline">
                    <i class="fa-solid fa-id-card"></i> {{ __('ui.business_profile') }}
                </a>
            </div>
        @endif

        {{-- KONTAK --}}
        <div class="space-y-4 pt-2">
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.account_contact') }}</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.email') }}</label>
                    <input type="email" name="email" required maxlength="255" class="{{ $input }}"
                           value="{{ old('email', $user->email) }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.phone') }}</label>
                    <input name="phone_number" maxlength="30" class="{{ $input }}"
                           value="{{ old('phone_number', $user->phone_number) }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.current_password') }}</label>
                <input type="password" name="current_password" autocomplete="current-password" class="{{ $input }}">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.email_change_needs_password') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                {{ __('ui.save_changes') }}
            </button>
            <a href="{{ route('account.settings') }}" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                {{ __('ui.cancel') }}
            </a>
        </div>
    </form>
@endsection