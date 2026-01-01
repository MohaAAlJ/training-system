<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sections;
use App\Models\Administrative;
use App\Models\Departments;
use App\Models\User;
use App\Models\Governorate; // استدعاء مودل المحافظات
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        // 1. جلب الكيانات الإدارية
        $amalHospital = Administrative::where('title', 'مستشفى الأمل')->first();
        $deirCenter = Administrative::where('title', 'مركز الدير')->first();
        $amalCity = Administrative::where('title', 'مدينة الأمل')->first();

        // 2. جلب الدوائر
        $deptPharmacy = Departments::where('title', 'الصيدلة')->first();
        $deptIT = Departments::where('title', 'تكنولوجيا المعلومات')->first();
        $deptMedia = Departments::where('title', 'الإعلام')->first();
        $deptLabs = Departments::where('title', 'المختبرات')->first();
        $deptAdmin = Departments::where('title', 'الإدارة العامة')->first();

        // 3. جلب المحافظات (للربط المكاني)
        // نستخدم البحث الجزئي لضمان العثور عليها سواء كانت مخزنة كـ JSON أو نص
        $govKhanYunis = Governorate::where('name', 'like', '%خانيونس%')->first(); // للأمل
        $govDeir = Governorate::where('name', 'like', '%دير البلح%')->first();   // للدير
        // قيمة افتراضية (1) في حال لم يتم العثور على المحافظة
        $khanYunisId = $govKhanYunis ? $govKhanYunis->id : 3; 
        $deirId = $govDeir ? $govDeir->id : 5;

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
                'gov_id' => $khanYunisId // محافظة خانيونس
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
                'gov_id' => $deirId // محافظة دير البلح
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
                    'password' => $password,
                    'role' => Constans::ROLE_SECTION,
                    'status' => 'active',
                ]
            );

            Sections::firstOrCreate(
                ['name_location' => $sec['name'], 'administrative_id' => $sec['admin_id']],
                [
                    'department_id' => $sec['dept_id'],
                    'user_id' => $hos->id,
                    'governorate_id' => $sec['gov_id'], // إضافة المحافظة
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

            Sections::firstOrCreate(
                ['name_location' => 'إدارة', 'administrative_id' => $admin->id],
                [
                    'department_id' => $deptAdmin->id,
                    'user_id' => null,
                    'governorate_id' => $govId, // إضافة المحافظة
                    'total_capacity' => 5,
                    'status' => 'active',
                ]
            );
        }
    }
}