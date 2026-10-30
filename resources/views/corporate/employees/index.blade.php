@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.employees') }}</h1>

    <p><a href="{{ route('corporate.employees.create') }}">{{ __('ui.add_employee') }}</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.full_name') }}</th>
                <th>{{ __('ui.email') }}</th>
                <th>{{ __('ui.department') }}</th>
                <th>{{ __('ui.position') }}</th>
                <th>{{ __('ui.role') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->displayName() }}</td>
                    <td>{{ $employee->user->email }}</td>
                    <td>{{ $employee->department ?? '-' }}</td>
                    <td>{{ $employee->position?->name ?? '-' }}</td>
                    <td>{{ __('ui.role_' . $employee->role_in_company) }}</td>
                    <td>{{ $employee->user->is_active ? __('ui.active') : __('ui.inactive') }}</td>
                    <td><a href="{{ route('corporate.employees.edit', $employee) }}">{{ __('ui.edit') }}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection