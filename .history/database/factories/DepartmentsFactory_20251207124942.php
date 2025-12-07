<?php

namespace Database\Factories;

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
            // Using your helper to get Arabic text
            'name' => 'قسم ' . arabicFaker()->realText(15),

            'description' => arabicFaker()->realText(50),
            
        ];
    }
}
