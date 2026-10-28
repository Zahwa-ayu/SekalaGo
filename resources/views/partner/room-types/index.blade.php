@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.room_types') }}: {{ $hotel->name }}</h1>

    <p>
        <a href="{{ route('partner.hotels.room-types.create', $hotel) }}">{{ __('ui.add_room_type') }}</a> |
        <a href="{{ route('partner.hotels.index') }}">{{ __('ui.my_hotels') }}</a>
    </p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.room_name') }}</th>
                <th>{{ __('ui.price_per_night') }}</th>
                <th>{{ __('ui.capacity') }}</th>
                <th>{{ __('ui.total_rooms') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roomTypes as $roomType)
                <tr>
                    <td>{{ $roomType->name }}</td>
                    <td>{{ format_price($roomType->base_price) }}</td>
                    <td>{{ $roomType->capacity }}</td>
                    <td>{{ $roomType->total_rooms }}</td>
                    <td>
                        <a href="{{ route('partner.room-types.edit', $roomType) }}">{{ __('ui.edit') }}</a> |
                        <a href="{{ route('partner.room-types.stocks.edit', $roomType) }}">{{ __('ui.manage_stock') }}</a>
                        <form method="POST" action="{{ route('partner.room-types.destroy', $roomType) }}"
                              style="display:inline"
                              onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button>{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('ui.no_room_types') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection