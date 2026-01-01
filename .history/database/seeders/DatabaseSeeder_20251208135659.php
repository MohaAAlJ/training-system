<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\InstitutionsSeeder;
use Database\Seeders\MajorsSeeder;
use Database\Seeders\InstitutionMajorSeeder;
use Database\Seeders\DepartmentsSeeder;
use Database\Seeders\TraineesSeeder;

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
        ]);
    }
}