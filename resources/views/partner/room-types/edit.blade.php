@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.edit_room_type') }}: {{ $hotel->name }}</h1>

    <form method="POST" action="{{ route('partner.room-types.update', $roomType) }}">
        @csrf
        @method('PUT')
        @include('partner.room-types._form')
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('partner.hotels.room-types.index', $hotel) }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection