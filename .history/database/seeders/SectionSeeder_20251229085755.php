<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\Administrative;
use App\Models\Department;
use App\Models\User;
use App\Models\Governorate; // استدعاء مودل المحافظات
use Illuminate\Support\Facades\Hash;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        // 1. جلب الكيانات الإدارية
        $amalHospital = Administrative::where('title', 'مستشفى الأمل')->first();
        $deirCenter = Administrative::where('title', 'مركز الدير')->first();
        $amalCity = Administrative::where('title', 'مدينة الأمل')->first();

        // 2. جلب الدوائر
        $deptPharmacy = Department::where('title', 'الصيدلة')->first();
        $deptIT = Department::where('title', 'تكنولوجيا المعلومات')->first();
        $deptMedia = Department::where('title', 'الإعلام')->first();
        $deptLabs = Department::where('title', 'المختبرات')->first();
        $deptAdmin = Department::where('title', 'الإدارة العامة')->first();

        // 3. جلب المحافظات (للربط المكاني)
        $govKhanYunis = Governorate::where('name', 'like', '%خانيونس%')->first();
        $govDeir = Governorate::where('name', 'like', '%دير البلح%')->first();

        // جلب معرفات المحافظات أو استخدام أول محافظة متاحة كخيار احتياطي
        $khanYunisId = $govKhanYunis?->id ?? Governorate::first()?->id;
        $deirId = $govDeir?->id ?? Governorate::first()?->id;

        if (!$amalHospital || !$deirCenter || !$amalCity) return;

        // 4. تعريف الأقسام مع المحافظة المناسبة
        $specificSections = [
            // --- أقسام مستشفى الأمل (خانيونس) ---
            [
                'name' => 'صيدلية الأمل',
                'admin_id' => $amalHospital->id,
                'dept_id' => $deptPharmacy->id,
                'email' => 'hos_pharma_amal@system.com',
                'user_name' => 'رئيس صيدلية الأمل',
                'gov_id' => $khanYunisId
            ],
            [
                'name' => 'الشبكات والدعم الفني',
                'admin_id' => $amalHospital->id,
                'dept_id' => $deptIT->id,
                'email' => 'hos_it_amal@system.com',
                'user_name' => 'رئيس IT الأمل',
                'gov_id' => $khanYunisId
            ],
            [
                'name' => 'مختبر المستشفى',
                'admin_id' => $amalHospital->id,
                'dept_id' => $deptLabs->id,
                'email' => 'hos_lab_amal@system.com',
                'user_name' => 'رئيس مختبر الأمل',
                'gov_id' => $khanYunisId
            ],

            // --- أقسام مركز الدير (دير البلح) ---
            [
                'name' => 'صيدلية دير البلح',
                'admin_id' => $deirCenter->id,
                'dept_id' => $deptPharmacy->id,
                'email' => 'hos_pharma_deir@system.com',
                'user_name' => 'رئيس صيدلية الدير',
                'gov_id' => $deirId
            ],
            [
                'name' => 'برمجيات (دير)',
                'admin_id' => $deirCenter->id,
                'dept_id' => $deptIT->id,
                'email' => 'hos_it_deir@system.com',
                'user_name' => 'رئيس برمجيات الدير',
                'gov_id' => $deirId
            ],

            // --- أقسام مدينة الأمل (خانيونس) ---
            [
                'name' => 'قسم الإعلام',
                'admin_id' => $amalCity->id,
                'dept_id' => $deptMedia->id,
                'email' => 'hos_media@system.com',
                'user_name' => 'رئيس قسم الإعلام',
                'gov_id' => $khanYunisId
            ],
        ];

        // إنشاء الأقسام المحددة
        foreach ($specificSections as $sec) {
            $hos = User::firstOrCreate(
                ['email' => $sec['email']],
                [
                    'name' => $sec['user_name'],
                    'user_name' => \Illuminate\Support\Str::slug($sec['email'], '_'),
                    'password' => $password,
                    'role' => User::ROLE_SECTION,
                    'status' => true,
                ]
            );

            Section::firstOrCreate(
                ['name_location' => $sec['name'], 'administrative_id' => $sec['admin_id']],
                [
                    'department_id' => $sec['dept_id'],
                    'user_id' => $hos->id,
                    'governorate_id' => $sec['gov_id'],
                    'total_capacity' => 10,
                    'status' => 'active',
                ]
            );
        }

        // إنشاء أقسام "إدارة" (بدون رئيس قسم) في كل الإدارات
        $allAdmins = Administrative::all();
        foreach ($allAdmins as $admin) {
            // تحديد المحافظة بناءً على اسم الإدارة (منطق بسيط)
            $govId = (str_contains($admin->title, 'الدير')) ? $deirId : $khanYunisId;

            Section::firstOrCreate(
                ['name_location' => 'إدارة', 'administrative_id' => $admin->id],
                [
                    'department_id' => $deptAdmin->id,
                    'user_id' => null,
                    'governorate_id' => $govId,
                    'total_capacity' => 1,
                    'status' => 'active',
                ]
            );
        }
    }
}
