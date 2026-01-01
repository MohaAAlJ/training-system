<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class SystemUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123'); 

        // 1. System Admin
        User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'مدير النظام (Admin)',
                'password' => $password,
                'role' => Constans::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        // 2. Ministry of Health (MOH)
        User::firstOrCreate(
            ['email' => 'moh@system.com'],
            [
                'name' => 'موظف وزارة الصحة',
                'password' => $password,
                'role' => Constans::ROLE_MOH,
                'status' => 'active',
            ]
        );

        // 3. General Training Manager (GTM)
        User::firstOrCreate(
            ['email' => 'gtm@system.com'],
            [
                'name' => 'مدير التدريب العام',
                'password' => $password,
                'role' => Constans::ROLE_GTM,
                'status' => 'active',
            ]
        );
    }
}
