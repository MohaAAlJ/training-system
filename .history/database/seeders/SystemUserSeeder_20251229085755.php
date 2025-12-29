<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        // 1. System Admin
        User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'مدير النظام (Admin)',
                'user_name' => 'admin_sys',
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'status' => true,
            ]
        );

        // 2. Ministry of Health (MOH)
        User::firstOrCreate(
            ['email' => 'moh@system.com'],
            [
                'name' => 'موظف وزارة الصحة',
                'user_name' => 'moh_user',
                'password' => $password,
                'role' => User::ROLE_MOH,
                'status' => true,
            ]
        );

        // 3. General Training Manager (GTM)
        User::firstOrCreate(
            ['email' => 'gtm@system.com'],
            [
                'name' => 'مدير التدريب العام',
                'user_name' => 'gtm_user',
                'password' => $password,
                'role' => User::ROLE_GTM,
                'status' => true,
            ]
        );
    }
}
