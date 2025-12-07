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
        // 1. Create 5 Directorates (Administratives)
        // 2. For each Directorate, create 3 Departments
        
        Administratives::factory()
            ->count(5)
            ->create()
            ->each(function ($admin) {
                Department::factory()
                    ->count(3) // 3 Departments per Admin
                    ->create([
                        'administrative_id' => $admin->id
                    ]);
            });
    }
}
