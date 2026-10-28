<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body>
    <nav>
        <a href="{{ route('home') }}">{{ config('app.name') }}</a>

        @include('partials.preferences')

        @auth
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button>{{ __('ui.logout') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}">{{ __('ui.login') }}</a>
            <a href="{{ route('register') }}">{{ __('ui.register') }}</a>
        @endauth
    </nav>

    <main>@yield('content')</main>
</body>
</html>