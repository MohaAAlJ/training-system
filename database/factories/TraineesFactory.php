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

        $link = DB::table('institution_major')->inRandomOrder()->first();

        if (!$link) {
            $institution = Institution::factory()->create();
            $major = Major::factory()->create();

            $pivotId = DB::table('institution_major')->insertGetId([
                'institution_id' => $institution->id,
                'major_id' => $major->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $institutionId = $institution->id;
        } else {
            $institutionId = $link->institution_id;
            $pivotId = $link->id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => $arabicFaker->name,
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'location'    => $arabicFaker->city, 

            'institution_id' => $institutionId,
            'institution_major_id' => $pivotId,
        ];
    }
}
