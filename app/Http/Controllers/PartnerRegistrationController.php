<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register-partner');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255'],
            'business_license_no' => ['required', 'string', 'max:100'],
            'bank_account_no'     => ['nullable', 'string', 'max:50'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'phone_number'        => ['nullable', 'string', 'max:20'],
            'password'            => ['required', 'confirmed', 'min:8'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'email'        => $data['email'],
                'password'     => $data['password'],
                'phone_number' => $data['phone_number'] ?? null,
                'user_type'    => 'partner',
            ]);

            Partner::create([
                'user_id'             => $user->id,
                'company_name'        => $data['company_name'],
                'business_license_no' => $data['business_license_no'],
                'bank_account_no'     => $data['bank_account_no'] ?? null,
            ]);
        });

        // Tidak login otomatis: akun aktif setelah diverifikasi admin
        return redirect()->route('login')->with('success', __('ui.partner_registered'));
    }
}