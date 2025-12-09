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

        $link = DB::table('institution_major')->inRandomOrder()->first();

        // 2. إذا لم يوجد ربط (الجدول فارغ)، نقوم بإنشاء ربط جديد فوراً
        if (!$link) {
            $institution = Institution::factory()->create();
            $major = Major::factory()->create();

            DB::table('institution_major')->insert([
                'institution_id' => $institution->id,
                'major_id' => $major->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $institutionId = $institution->id;
            $majorId = $major->id;
        } else {
            // إذا وجدنا بيانات جاهزة نستخدمها
            $institutionId = $link->institution_id;
            $majorId = $link->major_id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => $arabicFaker->name,
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'address'     => $arabicFaker->city,
            'institution_id' => $institutionId,
            'major_id' => $majorId,
        ];
    }
}
