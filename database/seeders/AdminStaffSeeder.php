<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminStaffSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['bos@sekalago.test', 'Pak Bos', 'Pemilik / Direktur', 'super_admin'],
            ['manager@sekalago.test', 'Bu Manager', 'Manager', 'manager'],
        ];

        foreach ($accounts as [$email, $name, $position, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['password' => 'password', 'user_type' => 'admin', 'is_active' => true]
            );

            $user->adminStaff()->updateOrCreate([], ['full_name' => $name, 'position' => $position, 'sub_role' => $role]);
        }

        // Admin utama: super admin
        User::where('email', 'admin@sekalago.test')->first()
            ?->adminStaff()->firstOrCreate([], ['full_name' => 'Super Admin', 'position' => 'Bos', 'sub_role' => 'super_admin']);
    }
}