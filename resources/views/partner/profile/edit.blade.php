@extends('layouts.app')

@section('content')
    @php
        $chip = 'px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap';

        $badge = match ($partner->verification_status) {
            'verified' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-amber-100 text-amber-700',
        };

        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.partner_profile') }}</h1>
            <span class="{{ $chip }} {{ $badge }}">{{ __('ui.verification_' . $partner->verification_status) }}</span>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if ($partner->verification_status === 'rejected' && $partner->verification_note)
            <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <span><strong>{{ __('ui.verification_note') }}:</strong> {{ $partner->verification_note }}</span>
            </div>
        @endif

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('partner.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- DATA USAHA --}}
            <div class="{{ $card }}">
                <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-building text-sagara-blue"></i> {{ __('ui.partner_business_data') }}</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.company_name') }}</label>
                        <input name="company_name" value="{{ old('company_name', $partner->company_name) }}" required class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.business_license_no') }}</label>
                        <input name="business_license_no" value="{{ old('business_license_no', $partner->business_license_no) }}" required class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.contact_person') }}</label>
                        <input name="contact_name" maxlength="255" value="{{ old('contact_name', $partner->contact_name) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.company_phone') }}</label>
                        <input name="phone" maxlength="20" value="{{ old('phone', $partner->phone) }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">{{ __('ui.company_address') }}</label>
                        <input name="address" maxlength="255" value="{{ old('address', $partner->address) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                        <select name="city_id" class="{{ $input }}">
                            <option value="">{{ __('ui.choose_city') }}</option>
                            @foreach (\App\Models\City::optionsFor($partner->city_id) as $city)
                                <option value="{{ $city->id }}" @selected((int) old('city_id', $partner->city_id) === $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.website') }}</label>
                        <input type="url" name="website" maxlength="255" value="{{ old('website', $partner->website) }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">{{ __('ui.supporting_document') }}</label>
                        <input type="file" name="supporting_document" accept=".pdf,image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                        @if ($partner->supporting_document_path)
                            <p class="text-[11px] text-emerald-600 mt-1"><i class="fa-solid fa-circle-check"></i> {{ __('ui.document_uploaded') }}</p>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-slate-400">{{ __('ui.partner_legal_locked_hint') }}</p>
            </div>

            {{-- REKENING --}}
            <div class="{{ $card }}">
                <div>
                    <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-building-columns text-sagara-blue"></i> {{ __('ui.payout_bank_account') }}</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ __('ui.bank_account_hint') }}</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.bank_name') }}</label>
                        <input name="bank_name" list="bank-list" maxlength="60" value="{{ old('bank_name', $partner->bank_name) }}" class="{{ $input }}">
                        <datalist id="bank-list">
                            @foreach (config('sekalago.banks') as $bank)
                                <option value="{{ $bank }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.bank_account_no') }}</label>
                        <input name="bank_account_no" inputmode="numeric" maxlength="50" value="{{ old('bank_account_no', $partner->bank_account_no) }}" class="{{ $input }} font-mono">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.bank_account_name') }}</label>
                        <input name="bank_account_name" maxlength="100" value="{{ old('bank_account_name', $partner->bank_account_name) }}" class="{{ $input }}">
                    </div>
                </div>
            </div>

            {{-- KOMISI --}}
            <div class="{{ $card }}">
                <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-percent text-sagara-blue"></i> {{ __('ui.commission_proposal') }}</h2>

                @if ($partner->hasSignedAgreement())
                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-700">
                        {{ __('ui.commission_locked', ['percent' => rtrim(rtrim(number_format($partner->commissionRate() * 100, 2, '.', ''), '0'), '.')]) }}
                    </div>
                @else
                    <p class="text-xs text-slate-500">{{ __('ui.commission_proposal_hint') }}</p>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $label }}">{{ __('ui.proposed_commission') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="proposed_commission" min="10" max="25" step="0.5"
                                       value="{{ old('proposed_commission', $partner->proposedPercent()) }}" class="{{ $input }}">
                                <span class="text-sm font-bold text-slate-500">%</span>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">{{ __('ui.commission_proposal_note') }}</label>
                            <input name="commission_proposal_note" maxlength="300" value="{{ old('commission_proposal_note', $partner->commission_proposal_note) }}" class="{{ $input }}">
                        </div>
                    </div>
                @endif
            </div>

            {{-- AKSI --}}
            <button class="px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                <i class="fa-solid fa-check"></i> {{ __('ui.save') }}
            </button>
        </form>
    </section>
@endsection