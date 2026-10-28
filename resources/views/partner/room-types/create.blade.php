@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.add_room_type') }}: {{ $hotel->name }}</h1>

    <form method="POST" action="{{ route('partner.hotels.room-types.store', $hotel) }}">
        @csrf
        @include('partner.room-types._form')
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('partner.hotels.room-types.index', $hotel) }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection