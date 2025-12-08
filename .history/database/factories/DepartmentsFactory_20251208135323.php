<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Administratives;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentsFactory extends Factory
{
    public function definition(): array
    {
        // Arabic departments with locations
        $departments = [
            'قسم الطوارئ - المبنى الرئيسي',
            'قسم العناية المركزة - الطابق الثاني',
            'قسم الباطنية - المبنى أ',
            'قسم الجراحة - المبنى ب',
            'قسم الأطفال - جناح الأطفال',
            'قسم النساء والولادة - الطابق الثالث',
            'قسم العظام - المبنى الرئيسي',
            'قسم القلب - مركز القلب',
            'قسم الكلى - وحدة الغسيل',
            'قسم العيون - العيادات الخارجية',
            'قسم الأنف والأذن والحنجرة - العيادات الخارجية',
            'قسم الأشعة - الطابق الأرضي',
            'قسم المختبر - المبنى الرئيسي',
            'قسم الصيدلية - الطابق الأرضي',
            'قسم العلاج الطبيعي - مركز التأهيل',
        ];

        return [
            'name_location' => $this->faker->randomElement($departments),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'total_capacity' => $this->faker->numberBetween(5, 20),
            'user_id' => User::factory(),
            'administrative_id' => Administratives::factory(),
        ];
    }
}
