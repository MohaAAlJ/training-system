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
            'status' => $this->faker->boolean, // Migration defines boolean
            'user_id' => User::factory(),
            'is_medical' => $this->faker->boolean,
        ];
    }
}
