<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administratives>
 */
class AdministrativesFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = fake('ar_SA');

        // Create the user who will be the head
        $user = User::factory()->create();

        return [
            // Example: "Directorate of Nursing" -> "مديرية التمريض"
            'title' => 'مديرية ' . $arabicFaker->realText(15),

            // Storing the name string as per your schema
            'head_of_administrative' => $user->name,

            // Linking the Foreign Key
            'user_id' => $user->id,
        ];
    }
}
