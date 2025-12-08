<?php

namespace Database\Factories;

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
            //
        ];
    }
}


// namespace Database\Factories;

// use App\Models\Applications;
// use App\Models\Departments;
// use App\Models\Trainees;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applications>
//  */
// class ApplicationsFactory extends Factory
// {
//     protected $model = Applications::class;

//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         $startDate = fake()->dateTimeBetween('now', '+1 month');
//         $endDate = fake()->dateTimeBetween($startDate, '+3 months');
//         $status = fake()->randomElement(['pending', 'approved', 'rejected', 'completed']);

//         $tags = [
//             'تدريب صيفي',
//             'تدريب تعاوني',
//             'تدريب ميداني',
//             'مستعجل',
//             'طالب متفوق',
//             'إعادة تدريب',
//         ];

//         return [
//             'trainee_id' => Trainees::factory(),
//             'department_id' => Departments::factory(),
//             'start_date' => $startDate->format('Y-m-d'),
//             'end_date' => $endDate->format('Y-m-d'),
//             'status' => $status,
//             'letter_image_path' => null,
//             'accepted_at' => $status === 'approved' ? fake()->dateTimeBetween('-1 month', 'now') : null,
//             'tags' => fake()->randomElement([null, implode(',', fake()->randomElements($tags, rand(1, 3)))]),
//         ];
//     }

//     /**
//      * Indicate that the application is pending.
//      */
//     public function pending(): static
//     {
//         return $this->state(fn (array $attributes) => [
//             'status' => 'pending',
//             'accepted_at' => null,
//         ]);
//     }

//     /**
//      * Indicate that the application is approved.
//      */
//     public function approved(): static
//     {
//         return $this->state(fn (array $attributes) => [
//             'status' => 'approved',
//             'accepted_at' => fake()->dateTimeBetween('-1 month', 'now'),
//         ]);
//     }

//     /**
//      * Indicate that the application is rejected.
//      */
//     public function rejected(): static
//     {
//         return $this->state(fn (array $attributes) => [
//             'status' => 'rejected',
//             'accepted_at' => null,
//         ]);
//     }

//     /**
//      * Indicate that the application is completed.
//      */
//     public function completed(): static
//     {
//         return $this->state(fn (array $attributes) => [
//             'status' => 'completed',
//             'accepted_at' => fake()->dateTimeBetween('-3 months', '-1 month'),
//         ]);
//     }
// }
