<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            RealDataSeeder::class,

            DepartmentsSeeder::class,

            TraineesSeeder::class,
            ApplicationsSeeder::class,
        ]);
    }
}