<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class CollegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = Institution::all();

        if ($institutions->isEmpty()) {
            $this->command->warn("No Institutions found. Please seed them first!");
            return;
        }

        foreach ($institutions as $institution) {
            // Each institution gets 3-5 colleges
            $collegeCount = rand(3, 5);
            
            for ($i = 0; $i < $collegeCount; $i++) {
                College::factory()->create([
                    'institution_id' => $institution->id,
                ]);
            }
        }

        $this->command->info("Colleges seeded successfully!");
    }
}
