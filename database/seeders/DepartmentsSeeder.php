<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Departments;
use App\Models\Administratives;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 5 Directorates (Administratives)
        // 2. For each Directorate, create 3 Departments

        Administratives::factory()
            ->count(5)
            ->create()
            ->each(function ($admin) {
                Departments::factory()
                    ->count(3) // 3 Departments per Admin
                    ->create([
                        'administrative_id' => $admin->id
                    ]);
            });
    }
}


// namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class DepartmentSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//             \App\Models\Departments::factory()->count(20)->create();


//     }
// }
