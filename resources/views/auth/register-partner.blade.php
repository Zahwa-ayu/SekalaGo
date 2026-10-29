@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.register_partner') }}</h1>
    <p><small>{{ __('ui.register_partner_hint') }}</small></p>

    <form method="POST" action="{{ route('register.partner.store') }}">
        @csrf

        <p>
            <input name="company_name" value="{{ old('company_name') }}"
                   placeholder="{{ __('ui.company_name') }}" required>
        </p>
        <p>
            <input name="business_license_no" value="{{ old('business_license_no') }}"
                   placeholder="{{ __('ui.business_license_no') }}" required>
        </p>
        <p>
            <input name="bank_account_no" value="{{ old('bank_account_no') }}"
                   placeholder="{{ __('ui.bank_account_no') }}">
        </p>
        <p>
            <input type="email" name="email" value="{{ old('email') }}"
                   placeholder="{{ __('ui.email') }}" required>
        </p>
        <p>
            <input name="phone_number" value="{{ old('phone_number') }}"
                   placeholder="{{ __('ui.phone') }}">
        </p>
        <p>
            <input type="password" name="password" placeholder="{{ __('ui.password') }}" required>
        </p>
        <p>
            <input type="password" name="password_confirmation"
                   placeholder="{{ __('ui.password_confirm') }}" required>
        </p>

        <button>{{ __('ui.register') }}</button>

        @foreach ($errors->all() as $error)
            <p style="color:red">{{ $error }}</p>
        @endforeach
    </form>
@endsection