<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineesFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = arabicFaker(); // Using your helper

        // Get a random record from the Pivot table (institution_major)
        // This ensures the student is registered in a major that actually exists in that university
        $link = DB::table('institution_major')->inRandomOrder()->first();

        // If no links exist, fallback to null (or seed them first)
        if (!$link) {
            // Fallback for safety, though seeding should prevent this
            $institutionId = 1;
            $majorId = 1; 
        } else {
            $institutionId = $link->institution_id;
            // Note: In your SQL dump, the column in 'trainees' is 'institution_major_id' (pivot id)
            // But usually, trainees map to a major_id. 
            // Based on your SQL dump structure:
            // `institution_id` and `institution_major_id` (likely referencing the pivot ID)
            $pivotId = $link->id; 
            
            // However, looking at your previous ERD, you had 'major_id'. 
            // I will use 'institution_major_id' as per your SQL dump Foreign Key.
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'), // 9 digits
            'full_name'   => $arabicFaker->name, // Arabic Name
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-18 years'), // At least 18 years old
            'location'    => $arabicFaker->city, // Gaza cities
            
            // Foreign Keys based on your SQL Dump
            'institution_id' => $institutionId,
            'institution_major_id' => $pivotId ?? null, // Linking to the pivot ID
        ];
    }
}