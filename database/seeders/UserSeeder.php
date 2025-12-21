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
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_ADMIN,
            'status' => 'active',
        ]);
        User::create([
            'name' => 'moha Admin',
            'email' => 'Moha@admins.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_ADMIN,
            'status' => 'active',
        ]);

        // 2. Training Manager
        User::create([
            'name' => 'Training Manager',
            'email' => 'manager@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_GTM,
            'status' => 'active',
        ]);

        // 3. Ministry (MOH) Access
        User::create([
            'name' => 'Ministry User',
            'email' => 'moh@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_MOH,
            'status' => 'active',
        ]);

        // 4. College Supervisor
        $collegeUser = User::create([
            'name' => 'College Supervisor',
            'email' => 'college@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_COLLEGE,
            'status' => 'active',
        ]);
        // Assign to existing College (e.g., ID 3: IT College at IUG)
        $college = College::find(3);
        if ($college) {
            $college->update(['user_id' => $collegeUser->id]);
        } else {
            // Fallback: Create valid college with institution_id
            College::create([
                'name' => 'كلية تجريبية',
                'institution_id' => 1, // Islamic University
                'user_id' => $collegeUser->id,
            ]);
        }


        // 5. Structure: Administrative -> Medical Administrative
        $hoaUser = User::create([
            'name' => 'Hospital Director',
            'email' => 'hospital@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_HOA,
            'status' => 'active',
        ]);

        $medicalHoaUser = User::create([
            'name' => 'Medical Director',
            'email' => 'medical@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_HOM,
            'status' => 'active',
        ]);

        $admin = Administrative::create([
            'title' => 'General Hospital',
            'user_id' => $hoaUser->id,
            'is_medical' => true,
            'medical_head_user_id' => $medicalHoaUser->id,
        ]);


        // 6. Department: Pharmacy (Medical)
        $hodUser = User::create([
            'name' => 'Pharmacy Head',
            'email' => 'pharmacy@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_DEPARTMENT,
            'status' => 'active',
        ]);

        $dept = Departments::create([
            'title' => 'Pharmacy',
            'is_medical' => true,
            'user_id' => $hodUser->id,
        ]);

        // 7. Section: ER Pharmacy (Location)
        $hosUser = User::create([
            'name' => 'ER Pharmacy Section Head',
            'email' => 'er_pharmacy@admin.com',
            'password' => Hash::make('123'),
            'role' => Constans::ROLE_SECTION,
            'status' => 'active',
        ]);

        Sections::create([
            'name_location' => 'ER Pharmacy Unit',
            'administrative_id' => $admin->id,
            'department_id' => $dept->id,
            'user_id' => $hosUser->id,
            'total_capacity' => 5,
            'status' => 'active',
        ]);
    }
}
