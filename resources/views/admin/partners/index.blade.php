@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.verify_partners') }}</h1>

    <p>
        @foreach (['pending', 'verified', 'rejected'] as $s)
            <a href="{{ route('admin.partners.index', ['status' => $s]) }}">
                {{ $status === $s ? '▶ ' : '' }}{{ __('ui.verification_' . $s) }} ({{ $counts->get($s, 0) }})
            </a> |
        @endforeach
        <a href="{{ route('admin.partners.index', ['status' => 'all']) }}">
            {{ $status === 'all' ? '▶ ' : '' }}{{ __('ui.all') }}
        </a>
    </p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.company_name') }}</th>
                <th>{{ __('ui.email') }}</th>
                <th>{{ __('ui.business_license_no') }}</th>
                <th>{{ __('ui.registered_at') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($partners as $partner)
                <tr>
                    <td>{{ $partner->company_name }}</td>
                    <td>{{ $partner->user->email }}</td>
                    <td>{{ $partner->business_license_no }}</td>
                    <td>{{ $partner->created_at->format('d/m/Y') }}</td>
                    <td>{{ __('ui.verification_' . $partner->verification_status) }}</td>
                    <td><a href="{{ route('admin.partners.show', $partner) }}">{{ __('ui.review') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('ui.no_partners') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection