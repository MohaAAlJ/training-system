<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Administrative;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        // 1. Arabic Name using helper
        $arabicName = arabicFaker()->name; 

        // 2. English Name using standard Faker
        $englishName = $this->faker->name;

        return [
            'name_location' => 'قسم ' . $arabicName . ' - Department ' . $englishName,
            
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(10, 50),
            
            // --- التعديل هنا ---
            // غير هذا السطر:
            // 'head_of_department_id' => User::factory(),
            
            // إلى هذا الاسم الموجود في قاعدتك:
            'user_id' => User::factory(), 

            'administrative_id' => Administratives::factory(),
        ];
    }
}