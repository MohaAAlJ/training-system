<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SystemUsersSeeder::class);

        $this->call(CollegeSupervisorsSeeder::class);


        $this->call(DepartmentsSeeder::class);
        $this->call(AdministrativesSeeder::class);
        $this->call(SectionsSeeder::class);
    }
}
