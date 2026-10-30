@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.edit_position') }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color:red">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('corporate.positions.update', $position) }}">
        @csrf
        @method('PUT')
        <p>
            <label>{{ __('ui.position_name') }}<br>
                <input name="name" value="{{ old('name', $position->name) }}" required>
            </label>
        </p>
        <p>
            <label>{{ __('ui.booking_limit') }}<br>
                <input type="number" name="booking_limit" min="0"
                       value="{{ old('booking_limit', $position->booking_limit) }}" required>
            </label>
        </p>
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('corporate.positions.index') }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection