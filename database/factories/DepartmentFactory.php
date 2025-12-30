<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
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
            'title' => 'قسم ' . $arabicFaker->realText(15),
            'user_id' => User::factory(),
            'head_of_department' => User::factory(),
            'medical_head_user_id' => $this->faker->boolean ? User::factory() : null,
            'is_medical' => $this->faker->boolean,
            'status' => true,
            'location' => $this->faker->address,
        ];
    }
}
