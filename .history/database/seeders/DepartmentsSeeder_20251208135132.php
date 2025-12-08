<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departments;
use App\Models\Administratives;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 Directorates (Administratives)
        // For each Directorate, create 3 Departments

        Administratives::factory()
            ->count(5)
            ->create()
            ->each(function ($admin) {
                Departments::factory()
                    ->count(3)
                    ->create([
                        'administrative_id' => $admin->id
                    ]);
            });
    }
}
