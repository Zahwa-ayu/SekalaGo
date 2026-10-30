@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.edit_employee') }}</h1>

    <form method="POST" action="{{ route('corporate.employees.update', $employee) }}">
        @csrf
        @method('PUT')
        @include('corporate.employees._form')
        <button>{{ __('ui.save') }}</button>
        <a href="{{ route('corporate.employees.index') }}">{{ __('ui.cancel') }}</a>
    </form>
@endsection