<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departments;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pharmacy (Medical)
        $hod1 = User::create([
            'name' => 'Dr. Layla (Pharmacy Head)',
            'email' => 'hod_pharmacy@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_DEPARTMENT_MANAGER,
            'status' => 'active',
        ]);

        Departments::create([
            'title' => 'Clinical Pharmacy Department',
            'is_medical' => true,
            'hod' => $hod1->id,
        ]);

        // 2. Nursing (Medical)
        $hod2 = User::create([
            'name' => 'Nurse Samia (Nursing Head)',
            'email' => 'hod_nursing@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_DEPARTMENT_MANAGER,
            'status' => 'active',
        ]);

        Departments::create([
            'title' => 'General Nursing Department',
            'is_medical' => true,
            'hod' => $hod2->id,
        ]);

        // 3. IT Support (Non-Medical)
        $hod3 = User::create([
            'name' => 'Eng. Tarek (IT Head)',
            'email' => 'hod_it@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_DEPARTMENT_MANAGER,
            'status' => 'active',
        ]);

        Departments::create([
            'title' => 'Information Technology',
            'is_medical' => false,
            'hod' => $hod3->id,
        ]);

        // 4. Human Resources (Non-Medical)
        $hod4 = User::create([
            'name' => 'Mr. Omar (HR Head)',
            'email' => 'hod_hr@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_DEPARTMENT_MANAGER,
            'status' => 'active',
        ]);

        Departments::create([
            'title' => 'Human Resources',
            'is_medical' => false,
            'hod' => $hod4->id,
        ]);
    }
}
