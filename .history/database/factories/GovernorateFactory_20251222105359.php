<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

class GovernorateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
        ];
    }
}
