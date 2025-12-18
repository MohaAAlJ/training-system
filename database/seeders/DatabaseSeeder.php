<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{

    $this->call([
        UserSeeder::class,
        DepartmentsSeeder::class,
    ]);


    $this->call(TraineesSeeder::class);

    $this->call(ApplicationsSeeder::class);
}
}
