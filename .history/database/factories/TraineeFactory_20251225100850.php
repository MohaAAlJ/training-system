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
class TraineesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'national_id' => $this->faker->unique()->numerify('##########'),
            'full_name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'dob' => $this->faker->date(),
            'governorate_id' => Governorate::inRandomOrder()->first()?->id ?? Governorate::factory(),
            'address' => $this->faker->address,
            'street' => $this->faker->streetName,
            'institution_id' => Institution::inRandomOrder()->first()?->id ?? 1, // Fallback to 1 if empty
            'college_id' => College::inRandomOrder()->first()?->id ?? 1,
            'major_id' => Major::inRandomOrder()->first()?->id ?? 1,
            'training_hours' => $this->faker->numberBetween(100, 500),
        ];
    }
}
