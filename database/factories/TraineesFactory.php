<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainees;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trainees>
 */
class TraineesFactory extends Factory
{
    protected $model = Trainees::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabicFaker = fake('ar_SA');

        return [
            'national_id' => fake()->unique()->numerify('##########'),
            'full_name' => $arabicFaker->name(),
            'phone_number' => fake()->numerify('05########'),
            'dob' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'address' => $arabicFaker->city(),
            'institution_id' => Institution::factory(),
            'major_id' => Major::factory(),
        ];
    }
}
