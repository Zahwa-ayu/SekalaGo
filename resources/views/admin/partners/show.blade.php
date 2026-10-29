@extends('layouts.app')

@section('content')
    <p><a href="{{ route('admin.partners.index') }}">&larr; {{ __('ui.verify_partners') }}</a></p>

    <h1>{{ $partner->company_name }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        <strong>{{ __('ui.status') }}:</strong> {{ __('ui.verification_' . $partner->verification_status) }}<br>
        @if ($partner->verification_note)
            <strong>{{ __('ui.verification_note') }}:</strong> {{ $partner->verification_note }}<br>
        @endif
        <strong>{{ __('ui.email') }}:</strong> {{ $partner->user->email }}<br>
        <strong>{{ __('ui.phone') }}:</strong> {{ $partner->user->phone_number ?? '-' }}<br>
        <strong>{{ __('ui.business_license_no') }}:</strong> {{ $partner->business_license_no }}<br>
        <strong>{{ __('ui.bank_account_no') }}:</strong> {{ $partner->bank_account_no ?? '-' }}<br>
        <strong>{{ __('ui.registered_at') }}:</strong> {{ $partner->created_at->format('d/m/Y H:i') }}<br>
        <strong>{{ __('ui.my_hotels') }}:</strong> {{ $partner->hotels_count }}
    </p>

    <h2>{{ __('ui.decision') }}</h2>

    @if ($partner->verification_status !== 'verified')
        <form method="POST" action="{{ route('admin.partners.verify', $partner) }}" style="display:inline">
            @csrf
            <button>{{ __('ui.verify') }}</button>
        </form>
    @endif

    @if ($partner->verification_status !== 'rejected')
        <form method="POST" action="{{ route('admin.partners.reject', $partner) }}">
            @csrf
            <p>
                <textarea name="verification_note" rows="3" maxlength="500" required
                          placeholder="{{ __('ui.rejection_reason_label') }}">{{ old('verification_note') }}</textarea>
            </p>
            <button>{{ __('ui.reject') }}</button>
        </form>
    @endif
@endsection