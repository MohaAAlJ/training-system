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
            'title' => $this->faker->jobTitle . ' Department',
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(10, 50),
            'current_capacity' => 0,
            'user_id' => User::factory(),
            'head_of_department' => User::factory(), // Added missing field based on model
            'medical_head_user_id' => $this->faker->boolean ? User::factory() : null, // Added missing field
            'is_medical' => $this->faker->boolean,
            'location' => $this->faker->address, // Added based on model fillable
        ];
    }
}
