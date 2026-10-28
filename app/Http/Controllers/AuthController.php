<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()    { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // is_active ikut dicek, akun nonaktif tidak bisa login
        if (Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route(Auth::user()->dashboardRoute()));
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah, atau akun nonaktif.'])
            ->onlyInput('email');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password'     => ['required', 'confirmed', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'email'        => $data['email'],
                'password'     => $data['password'],
                'phone_number' => $data['phone_number'] ?? null,
                'user_type'    => 'customer',
            ]);

            Customer::create([
                'user_id'   => $user->id,
                'full_name' => $data['full_name'],
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRoute());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}