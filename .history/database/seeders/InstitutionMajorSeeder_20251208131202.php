<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Major;
use Illuminate\Database\Seeder;

class InstitutionMajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = Institution::all();
        $majors = Major::all();

        if ($institutions->isEmpty() || $majors->isEmpty()) {
            $this->command->warn("No Institutions or Majors found. Please seed them first!");
            return;
        }

        foreach ($institutions as $institution) {
            
            // 3. Pick a random number (e.g., between 5 and 15) of majors
            // For smaller colleges, maybe pick fewer, for Universities pick more.
            $take = rand(5, 15); 

            // 4. Get random Major IDs
            $randomMajorIds = $majors->random($take)->pluck('id');

            // 5. Attach them to the institution (Populate the pivot table)
            // syncWithoutDetaching ensures we don't accidentally remove existing links if run twice
            $institution->majors()->syncWithoutDetaching($randomMajorIds);
        }
    }
}