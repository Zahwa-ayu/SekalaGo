@extends('layouts.app')

@section('content')
    <h1>{{ __('ui.login') }}</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.email') }}" required>
        <input type="password" name="password" placeholder="{{ __('ui.password') }}" required>
        <label><input type="checkbox" name="remember"> {{ __('ui.remember_me') }}</label>
        <button>{{ __('ui.login') }}</button>
        @error('email') <p>{{ $message }}</p> @enderror
    </form>
@endsection