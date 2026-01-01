<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    private static array $institutions = [
        'الجامعة الإسلامية بغزة',
        'جامعة الأزهر - غزة',
        'جامعة الأقصى',
        'جامعة القدس المفتوحة',
        'جامعة غزة',
        'جامعة فلسطين',
        'جامعة الإسراء',
        'الكلية الجامعية للعلوم التطبيقية',
        'كلية فلسطين التقنية - دير البلح',
        'كلية العودة الجامعية',
        'كلية تنمية القدرات',
    ];

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(self::$institutions),
        ];
    }
}
