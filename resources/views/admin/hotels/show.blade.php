@extends('layouts.app')

@section('content')
    <p><a href="{{ route('admin.hotels.index') }}">&larr; {{ __('ui.review_hotels') }}</a></p>

    <h1>{{ $hotel->name }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        <strong>{{ __('ui.status') }}:</strong> {{ __('ui.status_' . $hotel->status) }}<br>
        @if ($hotel->rejection_reason)
            <strong>{{ __('ui.rejection_reason_label') }}:</strong> {{ $hotel->rejection_reason }}<br>
        @endif
        <strong>{{ __('ui.partner') }}:</strong> {{ $hotel->partner->company_name }}<br>
        <strong>{{ __('ui.city') }}:</strong> {{ $hotel->city }}<br>
        <strong>{{ __('ui.address') }}:</strong> {{ $hotel->address ?? '-' }}<br>
        <strong>{{ __('ui.star_rating') }}:</strong> {{ $hotel->star_rating ?? '-' }}<br>
        <strong>{{ __('ui.facilities') }}:</strong>
        {{ $hotel->facilities->map(fn ($f) => $f->label)->join(', ') ?: '-' }}
    </p>

    <h2>{{ __('ui.translations') }}</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.language') }}</th>
                <th>{{ __('ui.hotel_name') }}</th>
                <th>{{ __('ui.description') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hotel->translations as $t)
                <tr>
                    <td>{{ config('sekalago.locales.' . $t->locale, $t->locale) }}
                        @if ($t->is_auto_translated) ({{ __('ui.auto_translated') }}) @endif
                    </td>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('ui.room_types') }}</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.room_name') }}</th>
                <th>{{ __('ui.price_per_night') }}</th>
                <th>{{ __('ui.capacity') }}</th>
                <th>{{ __('ui.total_rooms') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hotel->roomTypes as $roomType)
                <tr>
                    <td>{{ $roomType->name }}</td>
                    <td>{{ format_price($roomType->base_price) }}</td>
                    <td>{{ $roomType->capacity }}</td>
                    <td>{{ $roomType->total_rooms }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('ui.no_room_types') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>{{ __('ui.decision') }}</h2>

    @if ($hotel->status !== 'approved')
        <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}" style="display:inline">
            @csrf
            <button>{{ __('ui.approve') }}</button>
        </form>
    @endif

    @if ($hotel->status !== 'rejected')
        <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}">
            @csrf
            <p>
                <textarea name="rejection_reason" rows="3" maxlength="500" required
                          placeholder="{{ __('ui.rejection_reason_label') }}">{{ old('rejection_reason') }}</textarea>
            </p>
            <button>{{ __('ui.reject') }}</button>
        </form>
    @endif
@endsection