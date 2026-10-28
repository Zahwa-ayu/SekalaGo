<h1>{{ config('app.name') }}</h1>
@auth
    <p>Halo, {{ auth()->user()->email }} ({{ auth()->user()->user_type }})</p>
    <form method="POST" action="{{ route('logout') }}">@csrf <button>Logout</button></form>
@else
    <a href="{{ route('login') }}">Login</a> | <a href="{{ route('register') }}">Daftar</a>
@endauth