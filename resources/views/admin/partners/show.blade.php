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

    <h2>{{ __('ui.mou_title') }}</h2>

    @if ($agreement)
        <p>
            <strong>{{ __('ui.status') }}:</strong> {{ __('ui.mou_status_' . $agreement->status) }}
            ({{ $agreement->documentNumber() }})<br>
            <strong>{{ __('ui.commission_rate') }}:</strong> {{ $agreement->commissionPercent() }}%<br>
            <strong>{{ __('ui.payout_schedule') }}:</strong> {{ __('ui.payout_' . $agreement->payout_schedule) }}<br>
            <strong>{{ __('ui.free_cancel_days') }}:</strong> {{ $agreement->free_cancel_days }}<br>

            @if ($agreement->status === 'signed')
                <strong>{{ __('ui.signed_by') }}:</strong> {{ $agreement->signed_name }},
                {{ $agreement->signed_at->format('d/m/Y H:i') }} ({{ $agreement->signed_ip }})<br>

                <strong>{{ __('ui.document_integrity') }}:</strong>
                @if ($agreement->hashIsValid() === null)
                    -
                @elseif ($agreement->hashIsValid())
                    <span style="color:green">{{ __('ui.integrity_valid') }}</span>
                @else
                    <span style="color:red">{{ __('ui.integrity_invalid') }}</span>
                @endif
                <br>
                @if ($agreement->signature_hash)
                    <small>SHA-256: {{ $agreement->signature_hash }}</small>
                @endif
            @endif
        </p>

        <p>
            <a href="{{ route('admin.partners.agreement.download', $partner) }}">
                {{ $agreement->status === 'signed' ? __('ui.download_signed_pdf') : __('ui.download_pdf') }}
            </a>
        </p>

        <details>
            <summary>{{ __('ui.mou_content') }}</summary>
            <pre style="white-space:pre-wrap; border:1px solid #ccc; padding:10px">{{ $agreement->content }}</pre>
        </details>
    @else
        <p>{{ __('ui.mou_not_sent') }}</p>
    @endif

    @if (! $agreement || $agreement->status !== 'signed')
        <h3>{{ $partner->verification_status === 'verified' ? __('ui.mou_resend_btn') : __('ui.mou_send_btn') }}</h3>

        <form method="POST" action="{{ route('admin.partners.agreement', $partner) }}">
            @csrf
            <p>
                <label>{{ __('ui.commission_rate') }} (10 - 25)<br>
                    <input type="number" name="commission_percent" min="10" max="25" step="0.5"
                           value="{{ old('commission_percent', $agreement?->commissionPercent() ?? 15) }}" required>
                </label>
            </p>
            <p>
                <label>{{ __('ui.payout_schedule') }}<br>
                    <select name="payout_schedule">
                        @foreach (['weekly', 'biweekly', 'monthly'] as $option)
                            <option value="{{ $option }}"
                                @selected(old('payout_schedule', $agreement?->payout_schedule ?? 'monthly') === $option)>
                                {{ __('ui.payout_' . $option) }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </p>
            <p>
                <label>{{ __('ui.free_cancel_days') }}<br>
                    <input type="number" name="free_cancel_days" min="0" max="30"
                           value="{{ old('free_cancel_days', $agreement?->free_cancel_days ?? 3) }}" required>
                </label>
            </p>
            <button>{{ $partner->verification_status === 'verified' ? __('ui.mou_resend_btn') : __('ui.mou_send_btn') }}</button>
        </form>
    @endif

    <h2>{{ __('ui.decision') }}</h2>

    @if ($partner->verification_status === 'rejected')
        <form method="POST" action="{{ route('admin.partners.reopen', $partner) }}">
            @csrf
            <button>{{ __('ui.reopen') }}</button>
        </form>
    @else
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