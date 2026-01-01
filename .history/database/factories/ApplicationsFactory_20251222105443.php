<?php

namespace Database\Factories;

use App\Models\Applications;
use App\Models\Trainees;
use App\Models\Administrative;
use App\Models\Departments;
use App\Models\Sections;
use App\Helpers\Constans;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applications>
 */
class ApplicationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trainee_id' => Trainees::factory(),
            'administrative_id' => Administrative::factory(),
            'department_id' => Departments::factory(),
            'section_id' => Sections::factory(),
            'training_type' => $this->faker->word,
            'duration' => $this->faker->numberBetween(1, 12),
            'street' => $this->faker->streetName,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(Constans::STATUSES),
            'tags' => $this->faker->words(3, true),
            'application_letter' => $this->faker->paragraph,
            'accepted_at' => $this->faker->boolean ? now() : null,
        ];
    }
}
