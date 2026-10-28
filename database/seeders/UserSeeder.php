<?php

namespace Database\Seeders;

use App\Models\{AdminStaff, Company, CompanyUser, Customer, Partner, User};
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['email' => 'admin@sekalago.test', 'password' => 'password', 'user_type' => 'admin']);
        AdminStaff::create(['user_id' => $admin->id, 'full_name' => 'Admin SekalaGo', 'position' => 'Super Admin']);

        $partner = User::create(['email' => 'mitra@sekalago.test', 'password' => 'password', 'user_type' => 'partner']);
        Partner::create([
            'user_id' => $partner->id, 'company_name' => 'Hotel Sejahtera',
            'business_license_no' => 'NIB-0001', 'verification_status' => 'verified',
        ]);

        $customer = User::create(['email' => 'user@sekalago.test', 'password' => 'password', 'user_type' => 'customer']);
        Customer::create(['user_id' => $customer->id, 'full_name' => 'Budi Pelanggan']);

        $company = Company::create([
            'company_name' => 'PT Maju Jaya', 'corporate_code' => 'MAJU01',
            'deposit_balance' => 50000000, 'travel_policy_limit' => 2000000,
        ]);

        $finance = User::create(['email' => 'finance@sekalago.test', 'password' => 'password', 'user_type' => 'corporate']);
        CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $finance->id,
            'role_in_company' => 'admin_finance', 'department' => 'Finance',
        ]);

        $employee = User::create(['email' => 'karyawan@sekalago.test', 'password' => 'password', 'user_type' => 'corporate']);
        CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $employee->id,
            'role_in_company' => 'employee', 'department' => 'Marketing',
        ]);
    }
}