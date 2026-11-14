@extends('layouts.app')

@section('content')
    @php
        $chip = 'px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap';

        $badge = match ($partner->verification_status) {
            'verified' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-amber-100 text-amber-700',
        };
        $box   = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
    @endphp

<section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.partner_profile') }}</h1>
        <span class="{{ $chip }} {{ $badge }}">{{ __('ui.verification_' . $partner->verification_status) }}</span>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if ($partner->verification_status === 'rejected' && $partner->verification_note)
        <div class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <span><strong>{{ __('ui.verification_note') }}:</strong> {{ $partner->verification_note }}</span>
        </div>
    @endif

    @if ($errors->any())
        <ul class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- FORM --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
        <form method="POST" action="{{ route('partner.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="{{ $box }}">
                <label class="{{ $label }}"><i class="fa-solid fa-building text-sagara-blue"></i> {{ __('ui.company_name') }}</label>
                <input name="company_name" value="{{ old('company_name', $partner->company_name) }}" required class="{{ $input }}">
            </div>

            <div class="{{ $box }}">
                <label class="{{ $label }}"><i class="fa-solid fa-id-card text-sagara-blue"></i> {{ __('ui.business_license_no') }}</label>
                <input name="business_license_no" value="{{ old('business_license_no', $partner->business_license_no) }}" required class="{{ $input }}">
            </div>

            <div class="{{ $box }}">
                <label class="{{ $label }}"><i class="fa-solid fa-building-columns text-sagara-blue"></i> {{ __('ui.bank_account_no') }}</label>
                <input name="bank_account_no" value="{{ old('bank_account_no', $partner->bank_account_no) }}" class="{{ $input }}">
            </div>

            <button class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
                <i class="fa-solid fa-floppy-disk mr-1"></i> {{ __('ui.save') }}
            </button>
        </form>
    </div>
</section>
@endsection