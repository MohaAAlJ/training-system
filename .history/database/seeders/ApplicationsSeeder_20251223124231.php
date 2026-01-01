<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applications;
use App\Models\Trainees;
use App\Models\Sections;
use App\Helpers\Constans;
use Carbon\Carbon;

class ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب المتدربين (الذين أنشأناهم للتو)
        $traineeIT = Trainees::where('national_id', '400100100')->first();
        $traineePharma = Trainees::where('national_id', '400200200')->first();
        $traineeMedia = Trainees::where('national_id', '400300300')->first();
        $traineeLab = Trainees::where('national_id', '400400400')->first();
        $traineeAdmin = Trainees::where('national_id', '400500500')->first();

        // 2. جلب الأقسام المناسبة (نبحث بالاسم لضمان الدقة)
        $secSoftware = Sections::where('name_location', 'like', '%برمجيات%')->first(); // IT
        $secPharma = Sections::where('name_location', 'like', '%صيدلية الأمل%')->first(); // صيدلية
        $secMedia = Sections::where('name_location', 'like', '%الإعلام%')->first(); // إعلام
        $secLab = Sections::where('name_location', 'like', '%مختبر%')->first(); // مختبر
        $secAdmin = Sections::where('name_location', 'like', '%إدارة%')->first(); // إدارة

        // دالة مساعدة لإنشاء الطلب
        $createApp = function ($trainee, $section, $type, $status, $start, $end, $tags) {
            if (!$trainee || !$section) return;
            
            Applications::firstOrCreate(
                [
                    'trainee_id' => $trainee->id,
                    'section_id' => $section->id, // للتأكد من عدم التكرار
                ],
                [
                    'administrative_id' => $section->administrative_id, // نأخذها من القسم نفسه لضمان الصحة
                    'department_id' => $section->department_id,         // نأخذها من القسم نفسه
                    'training_type' => $type,
                    'status' => $status,
                    'duration' => 100,
                    'start_date' => $start,
                    'end_date' => $end,
                    'tags' => $tags,
                    'accepted_at' => ($status >= Constans::STATUS_CONFIRMATION) ? now() : null,
                ]
            );
        };

        // --- السيناريوهات ---

        // 1. IT: تدريب جامعي - قيد التدريب (Started)
        $createApp(
            $traineeIT, 
            $secSoftware, 
            Constans::TRAINING_TYPE_UNIVERSITY, 
            Constans::STATUS_STRATED_TRAINING, 
            Carbon::now()->subDays(10), 
            Carbon::now()->addDays(50), 
            'تدريب ميداني - جامعة'
        );

        // 2. صيدلة: مزاولة مهنة - طلب جديد (New)
        $createApp(
            $traineePharma, 
            $secPharma, 
            Constans::TRAINING_TYPE_PRACTICE, 
            Constans::STATUS_NEW, 
            Carbon::now()->addDays(5), 
            Carbon::now()->addMonths(6), 
            'امتياز - مزاولة مهنة'
        );

        // 3. إعلام: تدريب جامعي - قائمة انتظار (Waiting List)
        $createApp(
            $traineeMedia, 
            $secMedia, 
            Constans::TRAINING_TYPE_UNIVERSITY, 
            Constans::STATUS_WAITING_LIST, 
            Carbon::now()->addMonth(), 
            Carbon::now()->addMonths(2), 
            'انتظار الموافقة النهائية'
        );

        // 4. مختبرات: مزاولة مهنة - موافقة مبدئية (Initial Approve)
        $createApp(
            $traineeLab, 
            $secLab, 
            Constans::TRAINING_TYPE_PRACTICE, 
            Constans::STATUS_INITIAL_APPROVE, 
            Carbon::now()->addDays(1), 
            Carbon::now()->addMonths(6), 
            'بانتظار تأكيد الوزارة'
        );

        // 5. إدارة: تدريب جامعي - منتهي (Ended)
        $createApp(
            $traineeAdmin, 
            $secAdmin, 
            Constans::TRAINING_TYPE_UNIVERSITY, 
            Constans::STATUS_ENDED_TRAINING, 
            Carbon::now()->subMonths(3), 
            Carbon::now()->subDay(), 
            'تم الانتهاء بنجاح'
        );
    }
}