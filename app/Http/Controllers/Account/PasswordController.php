<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('account.password');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'different:current_password', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $request->user()->forceFill([
            'password'             => Hash::make($data['password']),
            'must_change_password' => false,
        ])->save();

        // Kembali ke halaman yang sempat dituju (jika dipaksa ganti password), kalau tidak ke Beranda
        return redirect()->intended(route('home'))->with('success', __('ui.password_changed'));
    }
}