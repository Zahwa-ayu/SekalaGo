@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.review_hotels') }}</h1>

    <p>
        @foreach (['pending', 'approved', 'rejected'] as $s)
            <a href="{{ route('admin.hotels.index', ['status' => $s]) }}">
                {{ $status === $s ? '▶ ' : '' }}{{ __('ui.status_' . $s) }} ({{ $counts->get($s, 0) }})
            </a> |
        @endforeach
        <a href="{{ route('admin.hotels.index', ['status' => 'all']) }}">
            {{ $status === 'all' ? '▶ ' : '' }}{{ __('ui.all') }}
        </a>
    </p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.hotel_name') }}</th>
                <th>{{ __('ui.partner') }}</th>
                <th>{{ __('ui.city') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.submitted_at') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hotels as $hotel)
                <tr>
                    <td>{{ $hotel->name }}</td>
                    <td>{{ $hotel->partner->company_name }}</td>
                    <td>{{ $hotel->city }}</td>
                    <td>{{ __('ui.status_' . $hotel->status) }}</td>
                    <td>{{ $hotel->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('admin.hotels.show', $hotel) }}">{{ __('ui.review') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('ui.no_hotels') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection