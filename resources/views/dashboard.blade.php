<h1>{{ $title }}</h1>
<p>Login sebagai: {{ auth()->user()->email }}</p>
<form method="POST" action="{{ route('logout') }}">@csrf <button>Logout</button></form>