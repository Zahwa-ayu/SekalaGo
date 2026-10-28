@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.register') }}</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input name="full_name" value="{{ old('full_name') }}" placeholder="{{ __('ui.full_name') }}" required>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required>
        <input name="phone_number" value="{{ old('phone_number') }}" placeholder="{{ __('ui.phone') }}">
        <input type="password" name="password" placeholder="{{ __('ui.password') }}" required>
        <input type="password" name="password_confirmation" placeholder="{{ __('ui.password_confirm') }}" required>
        <button>{{ __('ui.register') }}</button>
        @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
    </form>
@endsection