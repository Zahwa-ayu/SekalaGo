@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.hero_title') }}</h1>

    @include('partials.search-form')
@endsection