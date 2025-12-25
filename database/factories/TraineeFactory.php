<?php

namespace Database\Factories;

use App\Models\Trainee;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trainee>
 */
class TraineeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicFaker = fake('ar_SA');
        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name' => $arabicFaker->name,
            'phone_number' => $this->faker->numerify('97059#######'),
            'dob' => $this->faker->date('Y-m-d', '-20 years'),
            'governorate_id' => Governorate::factory(),
            'street' => $arabicFaker->streetAddress,
            'institution_id' => Institution::factory(),
            'college_id' => College::factory(),
            'major_id' => Major::factory(),
            'training_hours' => $this->faker->numberBetween(10, 200),
        ];
    }
}
