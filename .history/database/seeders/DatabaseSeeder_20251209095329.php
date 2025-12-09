<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        User::factory(5)->create();

        // 2. Main Data
        $this->call([
            InstitutionsSeeder::class,
            MajorsSeeder::class,
            InstitutionMajorSeeder::class, // Links Inst & Majors
            
            // 3. Departments (If you have the seeder from previous steps)
            DepartmentsSeeder::class, 

            // 4. Trainees
            TraineesSeeder::class,

            // 5. Applications
            ApplicationsSeeder::class,
        ]);
    }
}