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
            // 'administrative_id' => Administrative::factory(), // Removed
            // 'department_id' => Department::factory(), // Removed
            'section_id' => Section::factory(),
            'training_type' => $this->faker->randomElement([Application::PRACTICE, Application::UNIVERSITY]),
            'duration' => $this->faker->numberBetween(1, 12),
            'street' => $this->faker->streetName,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement([
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_CONFIRMATION,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING,
            ]),
            'tags' => $this->faker->words(3, true),
            'application_letter' => $this->faker->paragraph,
            'accepted_at' => $this->faker->boolean ? now() : null,
        ];
    }
}
