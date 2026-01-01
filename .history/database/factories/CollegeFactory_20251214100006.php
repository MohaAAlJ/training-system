<?php

namespace Database\Factories;

use App\Models\College;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollegeFactory extends Factory
{
    protected $model = College::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->words(3, true),
                'ar' => 'كلية ' . $this->faker->word(),
            ],
            'institution_id' => Institution::factory(),
            'user_id' => User::inRandomOrder()->first()?->id,
        ];
    }
}
