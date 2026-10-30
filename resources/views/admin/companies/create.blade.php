@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.add_company') }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('admin.companies.store') }}">
        @csrf

        <p>
            <label>{{ __('ui.company_name') }}<br>
                <input name="company_name" value="{{ old('company_name') }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.company_code') }} ({{ __('ui.optional') }})<br>
                <input name="corporate_code" value="{{ old('corporate_code') }}">
            </label>
        </p>
        <p>
            <label>{{ __('ui.default_limit') }}<br>
                <input type="number" name="travel_policy_limit" min="0" value="{{ old('travel_policy_limit', 2000000) }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.initial_deposit') }}<br>
                <input type="number" name="initial_deposit" min="0" value="{{ old('initial_deposit', 0) }}">
            </label>
        </p>

        <h3>{{ __('ui.finance_account') }}</h3>
        <p>
            <label>{{ __('ui.full_name') }}<br>
                <input name="finance_name" value="{{ old('finance_name') }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.email') }}<br>
                <input type="email" name="finance_email" value="{{ old('finance_email') }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.temp_password') }}<br>
                <input type="text" name="finance_password" required>
            </label>
        </p>

        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('admin.companies.index') }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection