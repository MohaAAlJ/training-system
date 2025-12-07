<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a dummy Head of Department User (if not exists)
        $headUserId = DB::table('users')->insertGetId([
            'name' => 'dr_ahmed',
            'email' => 'head_dept@example.com',
            'password' => Hash::make('password'), // Assuming you use Hash
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create a dummy Head of Administrative User
        $adminUserId = DB::table('users')->insertGetId([
            'name' => 'manager_khaled',
            'email' => 'admin_head@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Administratives (Directorates)
        // We need IDs from here to put into Departments
        $nursingAdminId = DB::table('Administratives')->insertGetId([
            'name' => 'Nursing Directorate', // دائرة التمريض
            'head_of_administrative_id' => $adminUserId,
        ]);

        $medicalAdminId = DB::table('Administratives')->insertGetId([
            'name' => 'Medical Services Directorate', // دائرة الخدمات الطبية
            'head_of_administrative_id' => $adminUserId,
        ]);

        $alliedAdminId = DB::table('Administratives')->insertGetId([
            'name' => 'Allied Health Directorate', // دائرة المهن الطبية المساندة
            'head_of_administrative_id' => $adminUserId,
        ]);

        // 4. Create Departments (Using the exact schema names)
        $departments = [
            // --- Nursing Departments ---
            [
                'name_location' => 'Emergency_Gaza', // (Name + Location)
                'status' => 'active',
                'total_capacity' => 20,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $nursingAdminId,
            ],
            [
                'name_location' => 'ICU_KhanYunis',
                'status' => 'active',
                'total_capacity' => 10,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $nursingAdminId,
            ],

            // --- Medical Departments ---
            [
                'name_location' => 'Internal Medicine_Shifa',
                'status' => 'active',
                'total_capacity' => 15,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $medicalAdminId,
            ],
            [
                'name_location' => 'Pediatrics_Nasser',
                'status' => 'inactive', // Example of inactive department
                'total_capacity' => 12,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $medicalAdminId,
            ],

            // --- Allied Health Departments ---
            [
                'name_location' => 'Physical Therapy_Rafah',
                'status' => 'active',
                'total_capacity' => 8,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $alliedAdminId,
            ],
            [
                'name_location' => 'Radiology_North',
                'status' => 'active',
                'total_capacity' => 5,
                'head_of_department_id' => $headUserId,
                'administrative_id' => $alliedAdminId,
            ],
        ];

        // Insert Data
        DB::table('Departments')->insert($departments);
    }
}
