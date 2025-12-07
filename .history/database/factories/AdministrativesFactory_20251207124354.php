<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administratives>
 */
class AdministrativesFactory extends Factory
{
    public function definition(): array
    {
        // Use fake('ar_SA') to get Arabic data
        $arabicFaker = fake('ar_SA');

        return [
            // Example: "مديرية الصحة"
            'name' => 'مديرية ' . $arabicFaker->realText(10),

            // Assuming the head is a user
            'head_of_administrative_id' => User::factory(),
        ];
    }
}
