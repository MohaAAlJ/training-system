<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applications;
use App\Models\Trainees;
use App\Models\Sections;
use App\Models\College;
use App\Models\Major;
use App\Helpers\Constans;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. مسح البيانات القديمة لضمان نظافة الاختبار
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Applications::truncate();
        Trainees::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. جلب الكليات التي ليدها مشرفين فقط
        $supervisedColleges = College::whereNotNull('user_id')->get();
        if ($supervisedColleges->isEmpty()) {
            return;
        }

        $supervisedCollegeIds = $supervisedColleges->pluck('id')->toArray();

        // 3. توليد بيانات جديدة
        // سننشئ عدد محدد من الطلبات للتجربة
        $totalToCreate = 60;

        for ($i = 0; $i < $totalToCreate; $i++) {
            // اختيار نوع التدريب عشوائياً
            $type = rand(Constans::TRAINING_TYPE_UNIVERSITY, Constans::TRAINING_TYPE_PRACTICE);

            $collegeId = null;
            $institutionId = null;
            $majorId = null;

            if ($type === Constans::TRAINING_TYPE_UNIVERSITY) {
                // للتدريب الجامعي: نختار فقط من الكليات التي لديها مشرف
                $college = $supervisedColleges->random();
                $collegeId = $college->id;
                $institutionId = $college->institution_id;

                // اختيار تخصص عشوائي من الكلية
                $major = Major::whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))->inRandomOrder()->first();
                $majorId = $major?->id;
            } else {
                // لمزاولة المهنة (MOH): لا نحتاج لبيانات الجامعة كما طلب المستخدم
                $collegeId = null;
                $institutionId = null;
                $majorId = null;
            }

            // إنشاء المتدرب
            $trainee = Trainees::create([
                'national_id' => '410' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'full_name' => "متدرب تجريبي رقم " . ($i + 1),
                'phone_number' => '97059' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dob' => Carbon::now()->subYears(rand(20, 30)),
                'governorate_id' => rand(1, 4),
                'street' => 'شارع عشوائي ' . $i,
                'institution_id' => $institutionId,
                'college_id' => $collegeId,
                'major_id' => $majorId,
                'training_hours' => rand(100, 300),
            ]);

            // اختيار قسم عشوائي
            $section = Sections::inRandomOrder()->first();
            if (!$section) continue;

            // توزيع الحالات لإثراء لوحة التحكم
            $rand = rand(1, 100);
            if ($rand <= 15) $status = Constans::STATUS_NEW;
            elseif ($rand <= 40) $status = Constans::STATUS_INITIAL_APPROVE; // حالة مهمة للمشرفين والوزارة
            elseif ($rand <= 55) $status = Constans::STATUS_CONFIRMATION;
            elseif ($rand <= 65) $status = Constans::STATUS_WAITING_LIST;
            elseif ($rand <= 90) $status = Constans::STATUS_STRATED_TRAINING;
            else $status = Constans::STATUS_ENDED_TRAINING;

            $start = Carbon::now()->addDays(rand(-60, 30));
            $duration = rand(30, 180);
            $end = (clone $start)->addDays($duration);

            Applications::create([
                'trainee_id' => $trainee->id,
                'section_id' => $section->id,
                'administrative_id' => $section->administrative_id,
                'department_id' => $section->department_id,
                'training_type' => $type,
                'status' => $status,
                'duration' => $duration,
                'start_date' => $start,
                'end_date' => $end,
                'tags' => 'بيانات اختبار جديدة',
                'accepted_at' => ($status >= Constans::STATUS_CONFIRMATION) ? now() : null,
            ]);
        }
    }
}
