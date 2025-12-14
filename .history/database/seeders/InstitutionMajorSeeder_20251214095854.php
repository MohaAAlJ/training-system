<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Major;
use Illuminate\Database\Seeder;

class InstitutionMajorSeeder extends Seeder
{
    /**
     * Run the database seeds - Now works with the new College model architecture.
     * Assigns random majors to each college within institutions.
     */
    public function run(): void
    {
        $colleges = College::all();
        $majors = Major::all();

        if ($colleges->isEmpty() || $majors->isEmpty()) {
            $this->command->warn("No Colleges or Majors found. Please seed them first!");
            return;
        }

        foreach ($colleges as $college) {
            // Randomly select between 5-15 majors for each college
            $take = rand(5, min(15, $majors->count()));
            $randomMajorIds = $majors->random($take)->pluck('id');

            // Attach majors to college via pivot table
            $college->majors()->syncWithoutDetaching($randomMajorIds);
        }

        $this->command->info('✅ College-Major relationships seeded successfully!');
    }
}