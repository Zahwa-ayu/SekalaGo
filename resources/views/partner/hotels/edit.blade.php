@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.edit_hotel') }}</h1>

    <form method="POST" action="{{ route('partner.hotels.update', $hotel) }}">
        @csrf
        @method('PUT')
        @include('partner.hotels._form')
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('partner.hotels.index') }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection