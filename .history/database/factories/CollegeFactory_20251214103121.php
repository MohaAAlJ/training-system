<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollegeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => [
                'ar' => 'كلية ' . $this->faker->word,
                'en' => 'College of ' . $this->faker->word
            ],
            // ربط الكلية بجامعة موجودة أو إنشاء واحدة جديدة
            'institution_id' => Institution::inRandomOrder()->first()?->id ?? Institution::factory(),

            // يمكن ترك المشرف فارغاً أو ربطه بمستخدم
            'user_id' => User::factory(),
        ];
    }
}
