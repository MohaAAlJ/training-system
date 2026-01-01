<?php

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineeFactory extends Factory // تأكد أن الاسم يطابق ملفك (TraineeFactory أو TraineesFactory)
{
    public function definition(): array
    {
        // 1. جلب رابط حقيقي موجود مسبقاً (تم إدخاله عبر RealDataSeeder)
        // هذا يضمن أن الطالب يسجل في تخصص موجود فعلاً في تلك الكلية
        $realLink = DB::table('college_major')->inRandomOrder()->first();

        if (!$realLink) {
            // حماية: في حال نسيت تشغيل RealDataSeeder
            throw new \Exception('Please run "php artisan db:seed --class=RealDataSeeder" first to populate majors.');
        }

        // 2. جلب معرف الكلية والتخصص من الرابط الحقيقي
        $collegeId = $realLink->college_id;
        $majorId = $realLink->major_id;

        // 3. جلب معرف المؤسسة (الجامعة) التابعة لها هذه الكلية
        $institutionId = College::find($collegeId)->institution_id;

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => arabicFaker()->name, // بيانات شخصية وهمية لكن الانتماء الأكاديمي حقيقي
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-19 years'),
            'address'     => 'Gaza Strip', 
            
            'institution_id' => $institutionId, 
            'college_id'     => $collegeId,
            'major_id'       => $majorId,
        ];
    }
}