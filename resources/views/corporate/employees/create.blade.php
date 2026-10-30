@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.add_employee') }}</h1>

    <form method="POST" action="{{ route('corporate.employees.store') }}">
        @csrf
        @include('corporate.employees._form')
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('corporate.employees.index') }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection