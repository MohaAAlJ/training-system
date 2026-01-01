<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Department;
use App\Models\User;
use App\Models\Administrative;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_location' => $this->faker->word . ' Section',
            'status' => $this->faker->boolean,
            'total_capacity' => $this->faker->numberBetween(5, 20),
            'department_id' => Department::factory(),
            'user_id' => User::factory(),
            'administrative_id' => Administrative::factory(),
            'governorate_id' => 1, // Default or factory if we make one
        ];
    }
}
