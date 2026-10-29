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
            @if (auth()->user()->user_type === 'customer')
                <a href="{{ route('bookings.index') }}">{{ __('ui.my_bookings') }}</a>
            @endif

            @if (auth()->user()->user_type === 'partner')
                <a href="{{ route('partner.hotels.index') }}">{{ __('ui.my_hotels') }}</a>
            @endif

            @if (auth()->user()->user_type === 'admin')
                <a href="{{ route('admin.hotels.index') }}">{{ __('ui.review_hotels') }}</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button>{{ __('ui.logout') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}">{{ __('ui.login') }}</a>
            <a href="{{ route('register') }}">{{ __('ui.register') }}</a>
        @endauth
    </nav>

    <main>
        @if (session('success'))
            <p style="color:green">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p style="color:red">{{ session('error') }}</p>
        @endif

        @yield('content')
    </main>
</body>
</html>