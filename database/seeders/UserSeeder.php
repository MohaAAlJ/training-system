<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Administrative;
use App\Models\Departments;
use App\Models\Sections;
use App\Models\College;
use App\Helpers\Constans;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Admin
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'Moha@admins.com'],
            [
                'name' => 'moha Admin',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        // 2. Training Manager
        User::firstOrCreate(
            ['email' => 'manager@admin.com'],
            [
                'name' => 'Training Manager',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_GTM,
                'status' => 'active',
            ]
        );

        // 3. Ministry (MOH) Access
        User::firstOrCreate(
            ['email' => 'moh@admin.com'],
            [
                'name' => 'Ministry User',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_MOH,
                'status' => 'active',
            ]
        );

        // 4. College Supervisor
        $collegeUser = User::firstOrCreate(
            ['email' => 'college@admin.com'],
            [
                'name' => 'College Supervisor',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_COLLEGE,
                'status' => 'active',
            ]
        );
        // Assign to existing College (ID 3: IT College)
        $college = College::find(3);
        if ($college) {
            $college->update(['user_id' => $collegeUser->id]);
        }

        // Another Supervisor for a different college
        $nursingSupervisor = User::firstOrCreate(
            ['email' => 'nursing@admin.com'],
            [
                'name' => 'Nursing Supervisor',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_COLLEGE,
                'status' => 'active',
            ]
        );
        // Assign to College (ID 6: Nursing College)
        $nursingCollege = College::find(6);
        if ($nursingCollege) {
            $nursingCollege->update(['user_id' => $nursingSupervisor->id]);
        }


        // 5. Structure: Administrative -> Medical Administrative
        $hoaUser = User::firstOrCreate(
            ['email' => 'hospital@admin.com'],
            [
                'name' => 'Hospital Director',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_HOA,
                'status' => 'active',
            ]
        );

        $medicalHoaUser = User::firstOrCreate(
            ['email' => 'medical@admin.com'],
            [
                'name' => 'Medical Director',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_HOM,
                'status' => 'active',
            ]
        );

        $admin = Administrative::firstOrCreate(
            ['title' => 'General Hospital'],
            [
                'user_id' => $hoaUser->id,
                'is_medical' => true,
                'medical_head_user_id' => $medicalHoaUser->id,
            ]
        );


        // 6. Department: Pharmacy (Medical)
        $hodUser = User::firstOrCreate(
            ['email' => 'pharmacy@admin.com'],
            [
                'name' => 'Pharmacy Head',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_DEPARTMENT,
                'status' => 'active',
            ]
        );

        $dept = Departments::firstOrCreate(
            ['title' => 'Pharmacy'],
            [
                'is_medical' => true,
                'user_id' => $hodUser->id,
            ]
        );

        $hodUserIT = User::firstOrCreate(
            ['email' => 'it@admin.com'],
            [
                'name' => 'IT Head',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_DEPARTMENT,
                'status' => 'active',
            ]
        );

        $deptIT = Departments::firstOrCreate(
            ['title' => 'IT Department'],
            [
                'is_medical' => false,
                'user_id' => $hodUserIT->id,
            ]
        );

        // 7. Section: ER Pharmacy (Location)
        $hosUser = User::firstOrCreate(
            ['email' => 'er_pharmacy@admin.com'],
            [
                'name' => 'ER Pharmacy Section Head',
                'password' => Hash::make('123'),
                'role' => Constans::ROLE_SECTION,
                'status' => 'active',
            ]
        );

        Sections::firstOrCreate(
            ['name_location' => 'ER Pharmacy Unit'],
            [
                'administrative_id' => $admin->id,
                'department_id' => $dept->id,
                'user_id' => $hosUser->id,
                'total_capacity' => 5,
                'status' => 'active',
            ]
        );
    }
}
