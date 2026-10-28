@extends('layouts.app')

@section('content')
    <h1>{{ __($title) }}</h1>
    <p>{{ __('ui.logged_in_as', ['email' => auth()->user()->email]) }}</p>
@endsection