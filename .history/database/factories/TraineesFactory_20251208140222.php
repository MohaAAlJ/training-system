<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineesFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = arabicFaker();

        // 1. محاولة جلب تخصص مربوط بجامعة من الجدول الوسيط
        $link = DB::table('institution_major')->inRandomOrder()->first();

        // 2. إذا لم يوجد ربط (الجدول فارغ)، نقوم بإنشاء ربط جديد فوراً
        if (!$link) {
            $institution = Institution::factory()->create(); // أو نختار أول جامعة
            $major = Major::factory()->create(); // أو نختار أول تخصص

            $pivotId = DB::table('institution_major')->insertGetId([
                'institution_id' => $institution->id,
                'major_id' => $major->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $institutionId = $institution->id;
        } else {
            // إذا وجدنا بيانات جاهزة نستخدمها
            $institutionId = $link->institution_id;
            $pivotId = $link->id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => $arabicFaker->name,
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'location'    => $arabicFaker->city, // تأكد أن لديك هذا العمود في جدولك

            // تعبئة المفاتيح الأجنبية بناءً على ملف الـ SQL الخاص بك
            'institution_id' => $institutionId,
            'institution_major_id' => $pivotId,
        ];
    }
}
