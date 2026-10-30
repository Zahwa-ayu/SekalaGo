@extends('layouts.app')

@section('content')
    <p><a href="{{ route('admin.companies.index') }}">&larr; {{ __('ui.companies') }}</a></p>

    <h1>{{ $company->company_name }} ({{ $company->corporate_code }})</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        <strong>{{ __('ui.deposit_balance') }}:</strong> {{ format_price($company->deposit_balance, 'IDR') }}<br>
        <strong>{{ __('ui.default_limit') }}:</strong> {{ format_price($company->travel_policy_limit, 'IDR') }}
    </p>

    <h2>{{ __('ui.topup') }}</h2>
    <form method="POST" action="{{ route('admin.companies.topup', $company) }}">
        @csrf
        <p>
            <input type="number" name="amount" min="1" value="{{ old('amount') }}"
                   placeholder="{{ __('ui.topup_amount') }}" required>
        </p>
        <p>
            <input name="note" maxlength="255" value="{{ old('note') }}"
                   placeholder="{{ __('ui.topup_note') }}">
        </p>
        <button>{{ __('ui.topup') }}</button>
    </form>

    <h2>{{ __('ui.employees') }}</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.full_name') }}</th>
                <th>{{ __('ui.email') }}</th>
                <th>{{ __('ui.department') }}</th>
                <th>{{ __('ui.position') }}</th>
                <th>{{ __('ui.role') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($company->companyUsers as $member)
                <tr>
                    <td>{{ $member->displayName() }}</td>
                    <td>{{ $member->user->email }}</td>
                    <td>{{ $member->department ?? '-' }}</td>
                    <td>{{ $member->position?->name ?? '-' }}</td>
                    <td>{{ __('ui.role_' . $member->role_in_company) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('ui.ledger') }}</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.date') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.amount') }}</th>
                <th>{{ __('ui.balance_after') }}</th>
                <th>{{ __('ui.note') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                <tr>
                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ __('ui.ledger_type_' . $tx->type) }}</td>
                    <td>{{ ($tx->amount > 0 ? '+' : '-') . ' ' . format_price(abs($tx->amount), 'IDR') }}</td>
                    <td>{{ format_price($tx->balance_after, 'IDR') }}</td>
                    <td>{{ $tx->note }} @if ($tx->creator) <small>({{ $tx->creator->email }})</small> @endif</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('ui.no_transactions') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection