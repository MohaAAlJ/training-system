<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Major;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineesFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = arabicFaker();

        // Get or create an institution
        $institution = Institution::inRandomOrder()->first();
        if (!$institution) {
            $institution = Institution::factory()->create();
        }

        // Get a college for this institution
        $college = $institution->colleges()->inRandomOrder()->first();
        if (!$college) {
            $college = College::factory()->create(['institution_id' => $institution->id]);
        }

        // Get a major that is linked to this college
        $major = $college->majors()->inRandomOrder()->first();

        // If no major linked to this college, create one
        if (!$major) {
            $major = Major::factory()->create();
            DB::table('college_major')->insert([
                'college_id' => $college->id,
                'major_id' => $major->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'full_name'   => $arabicFaker->name,
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'address'    => $arabicFaker->city,
            'college_id' => $college->id,
            'institution_id' => $institution->id,
            'major_id' => $major->id,
        ];
    }
}
