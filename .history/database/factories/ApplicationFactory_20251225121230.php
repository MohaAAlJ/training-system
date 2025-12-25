<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Trainee;
use App\Models\Administrative;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trainee_id' => Trainee::factory(),
            'administrative_id' => Administrative::factory(),
            'department_id' => Department::factory(),
            'section_id' => Section::factory(),
            'training_type' => $this->faker->randomElement(array_keys(Application::TRAINING_TYPES)),
            'duration' => $this->faker->numberBetween(1, 12),
            'street' => $this->faker->streetName,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(Application::STATUSES),
            'tags' => $this->faker->words(3, true),
            'application_letter' => $this->faker->paragraph,
            'accepted_at' => $this->faker->boolean ? now() : null,
        ];
    }
}
