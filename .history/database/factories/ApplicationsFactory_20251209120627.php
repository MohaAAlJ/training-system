<?php

namespace Database\Factories;

use App\Models\Applications;
use App\Models\Departments;
use App\Models\Trainees;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applications>
 */
class ApplicationsFactory extends Factory
{
    protected $model = Applications::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+3 months');
        $status = fake()->randomElement(['pending', 'active', 'rejected', 'completed', 'cancelled']);

        // Get or create a trainee
        $trainee = Trainees::inRandomOrder()->first();
        if (!$trainee) {
            $trainee = Trainees::factory()->create();
        }

        // Get or create a department
        $department = Departments::inRandomOrder()->first();
        if (!$department) {
            $department = Departments::factory()->create();
        }

        $tags = [
            'تدريب صيفي',
            'تدريب تعاوني',
            'تدريب ميداني',
            'مستعجل',
            'طالب متفوق',
            'إعادة تدريب',
        ];

        return [
            'trainee_id' => $trainee->id,
            'department_id' => $department->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => $status,
            'letter_image_path' => null,
            'accepted_at' => $status === 'active' ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'tags' => fake()->randomElement([null, implode(',', fake()->randomElements($tags, rand(1, 3)))]),
            'slug' => Str::slug($trainee->full_name) . '-' . Str::slug($department->name_location) . '-' . uniqid(),
        ];
    }

    /**
     * Indicate that the application is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'accepted_at' => null,
        ]);
    }

    /**
     * Indicate that the application is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'accepted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'accepted_at' => null,
        ]);
    }

    /**
     * Indicate that the application is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'accepted_at' => fake()->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }
}
