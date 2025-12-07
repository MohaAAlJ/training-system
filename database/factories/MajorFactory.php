<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    protected $model = Major::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => json_encode([
                'en' => $this->faker->word() . ' Studies',
                'ar' => $this->faker->word(),
            ]),
            'code' => strtoupper($this->faker->unique()->bothify('??###')),
        ];
    }
}
