@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.mou_title') }}</h1>

    @if (! $agreement)
        <p>{{ __('ui.mou_not_sent') }}</p>
    @else
        <p>
            <strong>{{ __('ui.status') }}:</strong> {{ __('ui.mou_status_' . $agreement->status) }}
            ({{ $agreement->documentNumber() }})
        </p>

        <table border="1" cellpadding="6">
            <tr>
                <td>{{ __('ui.commission_rate') }}</td>
                <td>{{ $agreement->commissionPercent() }}%</td>
            </tr>
            <tr>
                <td>{{ __('ui.payout_schedule') }}</td>
                <td>{{ __('ui.payout_' . $agreement->payout_schedule) }}</td>
            </tr>
            <tr>
                <td>{{ __('ui.free_cancel_days') }}</td>
                <td>{{ $agreement->free_cancel_days }}</td>
            </tr>
        </table>

        <p>
            <a href="{{ route('partner.agreement.download') }}">
                {{ $agreement->status === 'signed' ? __('ui.download_signed_pdf') : __('ui.download_pdf') }}
            </a>
        </p>

        <pre style="white-space:pre-wrap; border:1px solid #ccc; padding:10px">{{ $agreement->content }}</pre>

        @if ($agreement->status === 'sent')
            @if ($errors->any())
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('partner.agreement.sign') }}">
                @csrf
                <p>
                    <label>
                        <input type="checkbox" name="agree" value="1" required>
                        {{ __('ui.mou_agree') }}
                    </label>
                </p>
                <p>
                    <input name="signed_name" value="{{ old('signed_name') }}"
                           placeholder="{{ __('ui.mou_signed_name') }}" required>
                </p>
                <button>{{ __('ui.mou_sign_btn') }}</button>
            </form>
        @elseif ($agreement->status === 'signed')
            <p>
                <strong>{{ __('ui.signed_by') }}:</strong> {{ $agreement->signed_name }},
                {{ $agreement->signed_at->format('d/m/Y H:i') }}
            </p>
        @endif
    @endif
@endsection