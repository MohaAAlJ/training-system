<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    // قائمة مؤسسات التعليم العالي في غزة مع الترجمة
    private static array $institutions = [
        // الجامعات
        ['ar' => 'الجامعة الإسلامية بغزة', 'en' => 'Islamic University of Gaza'],
        ['ar' => 'جامعة الأزهر - غزة', 'en' => 'Al-Azhar University - Gaza'],
        ['ar' => 'جامعة الأقصى', 'en' => 'Al-Aqsa University'],
        ['ar' => 'جامعة القدس المفتوحة - فروع غزة', 'en' => 'Al-Quds Open University - Gaza'],
        ['ar' => 'جامعة غزة', 'en' => 'Gaza University'],
        ['ar' => 'جامعة فلسطين', 'en' => 'University of Palestine'],
        ['ar' => 'جامعة الإسراء', 'en' => 'Israa University'],

        // الكليات الجامعية
        ['ar' => 'الكلية الجامعية للعلوم التطبيقية', 'en' => 'University College of Applied Sciences'],
        ['ar' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس', 'en' => 'University College of Science and Technology - Khanyounis'],
        ['ar' => 'كلية فلسطين التقنية - دير البلح', 'en' => 'Palestine Technical College - Deir Al-Balah'],
        ['ar' => 'كلية العودة الجامعية', 'en' => 'Al-Awda University College'],
        ['ar' => 'كلية تنمية القدرات الجامعية', 'en' => 'Ability Development University College'],
        ['ar' => 'كلية الصحابة الجامعية', 'en' => 'Al-Sahaba University College'],
        ['ar' => 'كلية الزيتونة الجامعية', 'en' => 'Al-Zeitouna University College'],
        ['ar' => 'كلية الرباط الجامعية', 'en' => 'Al-Ribat University College'],
        ['ar' => 'كلية نماء للعلوم والتكنولوجيا', 'en' => 'Nama College of Science and Technology'],

        // كليات المجتمع
        ['ar' => 'كلية مجتمع الأقصى للدراسات المتوسطة', 'en' => 'Al-Aqsa Community College'],
    ];

    public function definition(): array
    {
        $institution = $this->faker->randomElement(self::$institutions);

        return [
            'name' => [
                'ar' => $institution['ar'],
                'en' => $institution['en'],
            ],
        ];
    }
}