@extends('layouts.app')

@section('content')
    <h1>{{ config('app.name') }}</h1>

    @auth
        <p>{{ __('ui.welcome', ['email' => auth()->user()->email]) }}</p>
    @endauth

    <p>{{ __('ui.sample_price') }}: <strong>{{ format_price(500000) }}</strong></p>
@endsection