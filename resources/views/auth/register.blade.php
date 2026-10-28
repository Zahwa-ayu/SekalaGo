<h1>Daftar {{ config('app.name') }}</h1>
<form method="POST" action="{{ route('register') }}">
    @csrf
    <input name="full_name" value="{{ old('full_name') }}" placeholder="Nama lengkap" required>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
    <input name="phone_number" value="{{ old('phone_number') }}" placeholder="No. HP">
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
    <button>Daftar</button>
    @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
</form>