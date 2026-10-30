@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.companies') }}</h1>

    <p><a href="{{ route('admin.companies.create') }}">{{ __('ui.add_company') }}</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.company_name') }}</th>
                <th>{{ __('ui.company_code') }}</th>
                <th>{{ __('ui.deposit_balance') }}</th>
                <th>{{ __('ui.default_limit') }}</th>
                <th>{{ __('ui.employees_count') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($companies as $company)
                <tr>
                    <td>{{ $company->company_name }}</td>
                    <td>{{ $company->corporate_code }}</td>
                    <td>{{ format_price($company->deposit_balance, 'IDR') }}</td>
                    <td>{{ format_price($company->travel_policy_limit, 'IDR') }}</td>
                    <td>{{ $company->company_users_count }}</td>
                    <td><a href="{{ route('admin.companies.show', $company) }}">{{ __('ui.review') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('ui.no_companies') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection