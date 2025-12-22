<?php

namespace Database\Factories;

use App\Models\Departments;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departments>
 */
class DepartmentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_location' => $this->faker->city, // Updated to match likely column name from model view earlier or verify
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(10, 50),
            'current_capacity' => 0,
            'user_id' => User::factory(),
            'administrative_id' => null, // Can be set via state
            'is_medical' => $this->faker->boolean,
        ];
    }
}
