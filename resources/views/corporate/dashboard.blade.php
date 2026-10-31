@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.corp_dashboard') }}: {{ $company->company_name }}</h1>

    <p>
        {{ $member->displayName() }}
        @if ($member->position) ({{ $member->position->name }}) @endif
        - {{ __('ui.role_' . $member->role_in_company) }}
    </p>

    <p>
        <strong>{{ __('ui.your_limit') }}:</strong> {{ format_price($limit, 'IDR') }}
        <small>({{ $member->position ? $member->position->name : __('ui.limit_from_default') }})</small>
    </p>

    @if ($budget !== null)
    <p>
        <strong>{{ __('ui.monthly_budget') }}:</strong> {{ format_price($budget, 'IDR') }}
        <small>
            ({{ __('ui.used_this_month') }}: {{ format_price($spent, 'IDR') }},
            {{ __('ui.remaining') }}: {{ format_price(max(0, $budget - $spent), 'IDR') }})
        </small>
    </p>
    @endif

    <p>
        <a href="{{ route('home') }}">{{ __('ui.find_hotel') }}</a> |
        <a href="{{ route('bookings.index') }}">{{ __('ui.my_bookings') }}</a>
    </p>

    @if ($member->isFinance())
        <table border="1" cellpadding="6">
            <tr>
                <td>{{ __('ui.deposit_balance') }}</td>
                <td><strong>{{ format_price($company->deposit_balance, 'IDR') }}</strong></td>
            </tr>
            <tr>
                <td>{{ __('ui.default_limit') }}</td>
                <td>{{ format_price($company->travel_policy_limit, 'IDR') }}</td>
            </tr>
            <tr>
                <td>{{ __('ui.employees_count') }}</td>
                <td>{{ $employeeCount }}</td>
            </tr>
            <tr>
                <td>{{ __('ui.pending_approvals_count') }}</td>
                <td>
                    <strong>{{ $pendingApprovals }}</strong>
                    @if ($pendingApprovals > 0)
                        - <a href="{{ route('corporate.bookings.index') }}">{{ __('ui.review') }}</a>
                    @endif
                </td>
            </tr>
        </table>

        <h2>{{ __('ui.recent_transactions') }}</h2>
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
                        <td>{{ $tx->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">{{ __('ui.no_transactions') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
@endsection