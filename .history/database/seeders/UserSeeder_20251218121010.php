<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@system.com',
            'password' => Hash::make('123'),
            'role' => 1,
            'status' => 'active',
        ]);

        // 2. Administrative Manager (مدير مستشفى الأمل)
        User::create([
            'name' => 'Hospital Manager',
            'email' => 'hosp@admin.com',
            'password' => Hash::make('123'),
            'role' => 2,
            'status' => 'active',
        ]);

        // 3. Department Manager (مدير عام الصيدلة)
        User::create([
            'name' => 'General Dept Manager',
            'email' => 'dept@general.com',
            'password' => Hash::make('123'),
            'role' => 3,
            'status' => 'active',
        ]);

        // 4. Section Head (رئيس صيدلية خانيونس)
        User::create([
            'name' => 'Section Head',
            'email' => 'section@branch.com',
            'password' => Hash::make('123'),
            'role' => 4,
            'status' => 'active',
        ]);
    }
}