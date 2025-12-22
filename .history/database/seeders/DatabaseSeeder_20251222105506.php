<?php

namespace Database\Seeders;

use App\Models\Administrative;
use App\Models\Applications;
use App\Models\Departments;
use App\Models\Governorate;
use App\Models\Sections;
use App\Models\Trainees;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Uncomment if needed
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Constans;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Test Users for each Role
        $this->createTestUsers();

        // 2. Generate Governorates (if not enough)
        if (Governorate::count() == 0) {
            Governorate::factory(5)->create();
        }

        // 3. Generate Administrative Units
        // Each needs a manager (User)
        $admins = Administrative::factory(5)
            ->for(User::factory()->department()) // Or specific role? Assuming department role fits or create generic
            ->create();

        // 4. Generate Departments
        // Each needs a head user
        $departments = Departments::factory(10)
            ->for(User::factory()->department())
            ->create();

        // 5. Generate Sections
        // Link to random department and administrative
        Sections::factory(20)->create([
            'department_id' => fn() => $departments->random()->id,
            'administrative_id' => fn() => $admins->random()->id,
        ]);

        // 6. Generate Trainees
        // Colleges, Institutions, Majors depend on migration seeds.
        // We will query them in factory or letting factory defaults handle 'random existing' logic if simplified,
        // but explicit creation helps ensure valid foreign keys if factory default wasn't strict.
        // My factory uses `inRandomOrder()->first()` which is good.
        $trainees = Trainees::factory(50)->create();

        // 7. Generate Applications
        // Link to existing trainees, depts, sections, admins
        Applications::factory(100)->create([
            'trainee_id' => fn() => $trainees->random()->id,
            'department_id' => fn() => $departments->random()->id,
            'administrative_id' => fn() => $admins->random()->id,
            'section_id' => fn() => Sections::inRandomOrder()->first()->id, // Get a section valid for that dept/admin ideally, but random is okay for dummy
        ]);
    }

    private function createTestUsers()
    {
        $password = Hash::make('password'); // Fixed password for testing

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => $password,
                'role' => Constans::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        // Department Head
        User::firstOrCreate(
            ['email' => 'dept@test.com'],
            [
                'name' => 'Department Head',
                'password' => $password,
                'role' => Constans::ROLE_DEPARTMENT,
                'status' => 'active',
            ]
        );

        // Section Head
        User::firstOrCreate(
            ['email' => 'section@test.com'],
            [
                'name' => 'Section Head',
                'password' => $password,
                'role' => Constans::ROLE_SECTION,
                'status' => 'active',
            ]
        );

        // MOH
        User::firstOrCreate(
            ['email' => 'moh@test.com'],
            [
                'name' => 'Ministry User',
                'password' => $password,
                'role' => Constans::ROLE_MOH,
                'status' => 'active',
            ]
        );

        // College Supervisor
        User::firstOrCreate(
            ['email' => 'college@test.com'],
            [
                'name' => 'College Supervisor',
                'password' => $password,
                'role' => Constans::ROLE_COLLEGE,
                'status' => 'active',
            ]
        );

        // Head Of Administration
        User::firstOrCreate(
            ['email' => 'hoa@test.com'],
            [
                'name' => 'Head Of Administration',
                'password' => $password,
                'role' => Constans::ROLE_HOA,
                'status' => 'active',
            ]
        );

        // Head Of Medical
        User::firstOrCreate(
            ['email' => 'hom@test.com'],
            [
                'name' => 'Head Of Medical',
                'password' => $password,
                'role' => Constans::ROLE_HOM,
                'status' => 'active',
            ]
        );

        // General Training Manager
        User::firstOrCreate(
            ['email' => 'gtm@test.com'],
            [
                'name' => 'General Training Manager',
                'password' => $password,
                'role' => Constans::ROLE_GTM,
                'status' => 'active',
            ]
        );
    }
}
