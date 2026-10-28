@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.my_hotels') }}</h1>

    @if ($partner->verification_status === 'verified')
        <p><a href="{{ route('partner.hotels.create') }}">{{ __('ui.add_hotel') }}</a></p>
    @else
        <p>{{ __('ui.not_verified_notice') }}</p>
    @endif

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.hotel_name') }}</th>
                <th>{{ __('ui.city') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hotels as $hotel)
                <tr>
                    <td>{{ $hotel->name }}</td>
                    <td>{{ $hotel->city }}</td>
                    <td>
                        {{ __('ui.status_' . $hotel->status) }}
                        @if ($hotel->status === 'rejected' && $hotel->rejection_reason)
                            <br><small>{{ __('ui.rejection_reason_label') }}: {{ $hotel->rejection_reason }}</small>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('partner.hotels.room-types.index', $hotel) }}">{{ __('ui.room_types') }}</a> |
                        <a href="{{ route('partner.hotels.edit', $hotel) }}">{{ __('ui.edit') }}</a>
                        <form method="POST" action="{{ route('partner.hotels.destroy', $hotel) }}"
                              style="display:inline"
                              onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button>{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('ui.no_hotels') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection