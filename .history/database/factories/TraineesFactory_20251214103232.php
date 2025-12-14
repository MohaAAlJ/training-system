<?php

namespace Database\Factories;

use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainees; 
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineesFactory extends Factory // أو TraineeFactory حسب اسم كلاس الموديل عندك
{
    public function definition(): array
    {
        // 1. محاولة العثور على تخصص مرتبط بكلية فعلياً (من البيانات الحقيقية أو السيدر)
        // نفترض أن اسم الجدول الوسيط هو college_major
        $link = DB::table('college_major')->inRandomOrder()->first();

        if ($link) {
            $collegeId = $link->college_id;
            $majorId = $link->major_id;
            // نجلب الجامعة التابعة لهذه الكلية
            $institutionId = College::find($collegeId)->institution_id;
        } else {
            // 2. خطة بديلة (Fallback) في حال كانت الجداول فارغة
            // نقوم بإنشاء سلسلة كاملة: جامعة -> كلية -> تخصص
            $college = College::factory()
                ->hasAttached(Major::factory()) // ربط تخصص بالكلية
                ->create();
            
            $collegeId = $college->id;
            $institutionId = $college->institution_id;
            $majorId = $college->majors()->first()->id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'), // 9 أرقام للهوية
            'full_name'   => $this->faker->name(), // أو arabicFaker()->name إذا كنت تستخدمه
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->dateTimeBetween('-24 years', '-19 years')->format('Y-m-d'),
            'address'     => $this->faker->address,
            
            // العلاقات المحدثة
            'institution_id' => $institutionId, // (اختياري، إذا كنت ما زلت تحتفظ بهذا العمود)
            'college_id'     => $collegeId,     // هذا هو الأساس الجديد
            'major_id'       => $majorId,
        ];
    }
}