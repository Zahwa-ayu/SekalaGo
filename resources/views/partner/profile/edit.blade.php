@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.partner_profile') }}</h1>

    <p>
        <strong>{{ __('ui.status') }}:</strong> {{ __('ui.verification_' . $partner->verification_status) }}
        @if ($partner->verification_status === 'rejected' && $partner->verification_note)
            <br><strong>{{ __('ui.verification_note') }}:</strong> {{ $partner->verification_note }}
        @endif
    </p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('partner.profile.update') }}">
        @csrf
        @method('PUT')

        <p>
            <label>{{ __('ui.company_name') }}<br>
                <input name="company_name" value="{{ old('company_name', $partner->company_name) }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.business_license_no') }}<br>
                <input name="business_license_no" value="{{ old('business_license_no', $partner->business_license_no) }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.bank_account_no') }}<br>
                <input name="bank_account_no" value="{{ old('bank_account_no', $partner->bank_account_no) }}">
            </label>
        </p>

        <button>{{ __('ui.save') }}</button>
    </form>
@endsection