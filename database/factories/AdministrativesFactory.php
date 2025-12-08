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


// namespace Database\Factories;

// use App\Models\Administratives;
// use App\Models\User;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administratives>
//  */
// class AdministrativesFactory extends Factory
// {
//     protected $model = Administratives::class;

//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         $titles = [
//             'مديرية التمريض',
//             'مديرية الخدمات الطبية',
//             'مديرية الموارد البشرية',
//             'مديرية الشؤون المالية',
//             'مديرية الجودة',
//             'مديرية التدريب والتطوير',
//             'مديرية الخدمات المساندة',
//             'مديرية تقنية المعلومات',
//             'مديرية المختبرات',
//             'مديرية الأشعة',
//         ];

//         return [
//             'title' => fake()->randomElement($titles),
//             'head_of_administrative' => fake('ar_SA')->name(),
//             'user_id' => User::factory(),
//         ];
//     }
// }
