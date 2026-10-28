<h1>Login {{ config('app.name') }}</h1>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <label><input type="checkbox" name="remember"> Ingat saya</label>
    <button>Masuk</button>
    @error('email') <p>{{ $message }}</p> @enderror
</form>