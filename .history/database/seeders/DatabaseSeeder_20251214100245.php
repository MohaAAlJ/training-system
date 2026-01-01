<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $this->call([
            UserSeeder::class,
        ]);
        User::factory(5)->create();

        // 2. Main Data - Real data with colleges and majors
        $this->call([
            RealDataSeeder::class,
        ]);

        // 3. Departments
        $this->call([
            DepartmentsSeeder::class,
        ]);

        // 4. Trainees
        $this->call([
            TraineesSeeder::class,
        ]);

        // 5. Applications
        $this->call([
            ApplicationsSeeder::class,
        ]);
    }
}
