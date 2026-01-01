<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollegeFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = arabicFaker();

        return [
            'name' => json_encode([
                'ar' => $arabicFaker->word,
                'en' => $this->faker->word,
            ]),
            'institution_id' => Institution::factory(),
            'user_id' => null,
        ];
    }
}
