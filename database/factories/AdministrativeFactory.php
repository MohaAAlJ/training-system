<?php

namespace Database\Factories;

use App\Models\Administrative;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrative>
 */
class AdministrativeFactory extends Factory
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
            'name' => 'مديرية ' . $arabicFaker->realText(15),
            'user_id' => User::factory(),
            'is_medical' => $this->faker->boolean,
            'medical_head_user_id' => $this->faker->boolean ? User::factory() : null,
        ];
    }
}
