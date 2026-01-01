<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    private static array $institutions = [
        ['ar' => 'الجامعة الإسلامية بغزة', 'en' => 'Islamic University of Gaza'],
        ['ar' => 'جامعة الأزهر - غزة', 'en' => 'Al-Azhar University - Gaza'],
        ['ar' => 'جامعة الأقصى', 'en' => 'Al-Aqsa University'],
        ['ar' => 'جامعة القدس المفتوحة', 'en' => 'Al-Quds Open University'],
        ['ar' => 'جامعة غزة', 'en' => 'Gaza University'],
        ['ar' => 'جامعة فلسطين', 'en' => 'University of Palestine'],
        ['ar' => 'جامعة الإسراء', 'en' => 'Israa University'],
        ['ar' => 'الكلية الجامعية للعلوم التطبيقية', 'en' => 'University College of Applied Sciences'],
        ['ar' => 'كلية فلسطين التقنية - دير البلح', 'en' => 'Palestine Technical College'],
        ['ar' => 'كلية العودة الجامعية', 'en' => 'Al-Awda University College'],
        ['ar' => 'كلية تنمية القدرات', 'en' => 'Ability Development College'],
    ];

    public function definition(): array
    {
        $selectedUni = $this->faker->randomElement(self::$institutions);

        return [
            'name' => [
                'ar' => $selectedUni['ar'],
                'en' => $selectedUni['en'],
            ],
        ];
    }
}
