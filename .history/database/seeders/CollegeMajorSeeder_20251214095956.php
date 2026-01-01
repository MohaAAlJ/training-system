<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Major;
use Illuminate\Database\Seeder;

class CollegeMajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
            // Each college gets 3-8 random majors
            $take = rand(3, 8);
            $randomMajorIds = $majors->random(min($take, $majors->count()))->pluck('id');

            $college->majors()->syncWithoutDetaching($randomMajorIds);
        }

        $this->command->info("College-Major relationships seeded successfully!");
    }
}
