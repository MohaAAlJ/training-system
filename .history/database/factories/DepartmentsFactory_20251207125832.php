<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Administratives;

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
        // 1. Get Arabic name using your custom helper
        $arabicName = arabicFaker()->name; 

        // 2. Get English name using standard Faker
        $englishName = $this->faker->name;

        return [
            // Combine them: "قسم [Arabic] - Department [English]"
            'name_location' => 'قسم ' . $arabicName . ' - Department ' . $englishName,
            
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(10, 50),
            
            // Relationships
            'head_of_department_id' => User::factory(),
            'administrative_id' => Administrative::factory(),
        ];
    }
}
