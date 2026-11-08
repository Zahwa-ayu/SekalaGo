<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user     = User::where('email', $data['email'])->first();
        $demoLink = null;

        if ($user) {
            $token = Password::broker()->createToken($user);
            $user->sendPasswordResetNotification($token);

            // Mode demo: tampilkan tautan di layar karena belum ada server email
            if (config('sekalago.show_reset_link')) {
                $demoLink = route('password.reset', ['token' => $token, 'email' => $user->email]);
            }
        }

        // Pesan sama untuk email terdaftar maupun tidak, supaya daftar akun tidak bisa ditebak
        return back()
            ->with('success', __('ui.reset_link_generic'))
            ->with('demo_link', $demoLink);
    }
}