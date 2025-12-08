<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        // قائمة جامعات وكليات غزة الحقيقية (عربي - إنجليزي)
        $universities = [
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

        // اختيار جامعة عشوائية من القائمة أعلاه
        $selectedUni = $this->faker->randomElement($universities);

        return [
            'name' => [
                'ar' => $selectedUni['ar'],
                'en' => $selectedUni['en'],
            ],
            
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}