<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Major;
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

        // Get a major that is linked to this institution
        $majorLink = DB::table('institution_major')
            ->where('institution_id', $institution->id)
            ->inRandomOrder()
            ->first();

        // If no major linked to this institution, create one
        if (!$majorLink) {
            $major = Major::factory()->create();
            DB::table('institution_major')->insert([
                'institution_id' => $institution->id,
                'major_id' => $major->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $majorId = $major->id;
        } else {
            $majorId = $majorLink->major_id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => $arabicFaker->name,
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'address'    => $arabicFaker->city,
            'institution_id' => $institution->id,
            'major_id' => $majorId,
        ];
    }
}
