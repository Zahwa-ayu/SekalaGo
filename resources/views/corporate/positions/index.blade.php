@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.positions_limits') }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>{{ __('ui.default_limit') }}</h2>
    <form method="POST" action="{{ route('corporate.policy.update') }}">
        @csrf
        @method('PUT')
        <input type="number" name="travel_policy_limit" min="0"
               value="{{ old('travel_policy_limit', $company->travel_policy_limit) }}" required>
        <button>{{ __('ui.save') }}</button>
    </form>
    <p><small>{{ __('ui.default_limit_hint') }}</small></p>

    <h2>{{ __('ui.add_position') }}</h2>
    <form method="POST" action="{{ route('corporate.positions.store') }}">
        @csrf
        <input name="name" value="{{ old('name') }}" placeholder="{{ __('ui.position_name') }}" required>
        <input type="number" name="booking_limit" min="0" value="{{ old('booking_limit') }}"
               placeholder="{{ __('ui.booking_limit') }}" required>
        <button>{{ __('ui.save') }}</button>
    </form>

    <h2>{{ __('ui.positions_limits') }}</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>{{ __('ui.position_name') }}</th>
                <th>{{ __('ui.booking_limit') }}</th>
                <th>{{ __('ui.employees_count') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($positions as $position)
                <tr>
                    <td>{{ $position->name }}</td>
                    <td>{{ format_price($position->booking_limit, 'IDR') }}</td>
                    <td>{{ $position->members_count }}</td>
                    <td>
                        <a href="{{ route('corporate.positions.edit', $position) }}">{{ __('ui.edit') }}</a>
                        <form method="POST" action="{{ route('corporate.positions.destroy', $position) }}"
                              style="display:inline"
                              onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button>{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('ui.no_positions') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection