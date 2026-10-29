<?php

namespace Database\Seeders;

use App\Models\AdminStaff;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\PartnerAgreement;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin internal
        $admin = User::create([
            'email'     => 'admin@sekalago.test',
            'password'  => 'password',
            'user_type' => 'admin',
        ]);
        AdminStaff::create([
            'user_id'   => $admin->id,
            'full_name' => 'Admin SekalaGo',
            'position'  => 'Super Admin',
        ]);

        // Mitra 1: sudah terverifikasi dan MOU sudah ditandatangani (bisa langsung membuat hotel)
        $partnerUser = User::create([
            'email'     => 'mitra@sekalago.test',
            'password'  => 'password',
            'user_type' => 'partner',
        ]);
        $partner = Partner::create([
            'user_id'             => $partnerUser->id,
            'company_name'        => 'Hotel Sejahtera',
            'business_license_no' => 'NIB-0001',
            'verification_status' => 'verified',
        ]);
        PartnerAgreement::create([
            'partner_id'       => $partner->id,
            'version'          => 1,
            'commission_rate'  => 0.15,
            'payout_schedule'  => 'monthly',
            'free_cancel_days' => 3,
            'content'          => 'MOU contoh dari seeder.',
            'status'           => 'signed',
            'sent_at'          => now(),
            'signed_at'        => now(),
            'signed_name'      => 'Hotel Sejahtera',
            'signed_ip'        => '127.0.0.1',
        ]);

        // Mitra 2: baru mendaftar, menunggu verifikasi admin (belum bisa login)
        $pendingUser = User::create([
            'email'     => 'mitra2@sekalago.test',
            'password'  => 'password',
            'user_type' => 'partner',
        ]);
        Partner::create([
            'user_id'             => $pendingUser->id,
            'company_name'        => 'Homestay Mawar',
            'business_license_no' => 'NIB-0002',
            'verification_status' => 'pending',
        ]);

        // Pelanggan B2C
        $customerUser = User::create([
            'email'     => 'user@sekalago.test',
            'password'  => 'password',
            'user_type' => 'customer',
        ]);
        Customer::create([
            'user_id'   => $customerUser->id,
            'full_name' => 'Budi Pelanggan',
        ]);

        // Corporate B2B
        $company = Company::create([
            'company_name'        => 'PT Maju Jaya',
            'corporate_code'      => 'MAJU01',
            'deposit_balance'     => 50000000,
            'travel_policy_limit' => 2000000,
        ]);

        $finance = User::create([
            'email'     => 'finance@sekalago.test',
            'password'  => 'password',
            'user_type' => 'corporate',
        ]);
        CompanyUser::create([
            'company_id'      => $company->id,
            'user_id'         => $finance->id,
            'role_in_company' => 'admin_finance',
            'department'      => 'Finance',
        ]);

        $employee = User::create([
            'email'     => 'karyawan@sekalago.test',
            'password'  => 'password',
            'user_type' => 'corporate',
        ]);
        CompanyUser::create([
            'company_id'      => $company->id,
            'user_id'         => $employee->id,
            'role_in_company' => 'employee',
            'department'      => 'Marketing',
        ]);
    }
}