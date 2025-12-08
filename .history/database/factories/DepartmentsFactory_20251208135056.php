<?php
namespace Database\Factories;

use App\Models\User;
use App\Models\Administratives;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentsFactory extends Factory
{
    public function definition(): array
    {
        // 1. Arabic Name using helper
        $arabicName = arabicFaker()->name;

        // 2. English Name using standard Faker
        $englishName = $this->faker->name;

        return [
            'name_location' => 'قسم ' . $arabicName . $englishName,

            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(10, 50),
            'user_id' => User::factory(),

            'administrative_id' => Administratives::factory(),
        ];
    }
}

// namespace Database\Factories;

// use App\Models\Administratives;
// use App\Models\Departments;
// use App\Models\User;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departments>
//  */
// class DepartmentsFactory extends Factory
// {
//     protected $model = Departments::class;

//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         $departments = [
//             'قسم الطوارئ - المبنى الرئيسي',
//             'قسم العناية المركزة - الطابق الثاني',
//             'قسم الباطنية - المبنى أ',
//             'قسم الجراحة - المبنى ب',
//             'قسم الأطفال - جناح الأطفال',
//             'قسم النساء والولادة - الطابق الثالث',
//             'قسم العظام - المبنى الرئيسي',
//             'قسم القلب - مركز القلب',
//             'قسم الكلى - وحدة الغسيل',
//             'قسم العيون - العيادات الخارجية',
//             'قسم الأنف والأذن والحنجرة - العيادات الخارجية',
//             'قسم الأشعة - الطابق الأرضي',
//             'قسم المختبر - المبنى الرئيسي',
//             'قسم الصيدلية - الطابق الأرضي',
//             'قسم العلاج الطبيعي - مركز التأهيل',
//         ];

//         return [
//             'name_location' => fake()->randomElement($departments),
//             'status' => fake()->randomElement(['active', 'inactive']),
//             'total_capacity' => fake()->numberBetween(5, 20),
//             'user_id' => User::factory(),
//             'administrative_id' => Administratives::factory(),
//         ];
//     }
// }
