<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sections;
use App\Models\Administrative;
use App\Models\Departments;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        // جلب المعرفات
        $amalHospital = Administrative::where('title', 'مستشفى الأمل')->first();
        $deirCenter = Administrative::where('title', 'مركز الدير')->first();
        $amalCity = Administrative::where('title', 'مدينة الأمل')->first();

        $deptPharmacy = Departments::where('title', 'الصيدلة')->first();
        $deptIT = Departments::where('title', 'تكنولوجيا المعلومات')->first();
        $deptMedia = Departments::where('title', 'الإعلام')->first();
        $deptLabs = Departments::where('title', 'المختبرات')->first();
        $deptAdmin = Departments::where('title', 'الإدارة العامة')->first();

        if (!$amalHospital || !$deirCenter || !$amalCity) return;

        $specificSections = [
            // مستشفى الأمل
            [
                'name' => 'صيدلية الأمل',
                'admin_id' => $amalHospital->id, 'dept_id' => $deptPharmacy->id,
                'email' => 'hos_pharma_amal@system.com', 'user_name' => 'رئيس صيدلية الأمل'
            ],
            [
                'name' => 'الشبكات والدعم الفني',
                'admin_id' => $amalHospital->id, 'dept_id' => $deptIT->id,
                'email' => 'hos_it_amal@system.com', 'user_name' => 'رئيس IT الأمل'
            ],
            [
                'name' => 'مختبر المستشفى',
                'admin_id' => $amalHospital->id, 'dept_id' => $deptLabs->id,
                'email' => 'hos_lab_amal@system.com', 'user_name' => 'رئيس مختبر الأمل'
            ],

            // مركز الدير
            [
                'name' => 'صيدلية دير البلح',
                'admin_id' => $deirCenter->id, 'dept_id' => $deptPharmacy->id,
                'email' => 'hos_pharma_deir@system.com', 'user_name' => 'رئيس صيدلية الدير'
            ],
            [
                'name' => 'برمجيات (دير)',
                'admin_id' => $deirCenter->id, 'dept_id' => $deptIT->id,
                'email' => 'hos_it_deir@system.com', 'user_name' => 'رئيس برمجيات الدير'
            ],

            // مدينة الأمل
            [
                'name' => 'قسم الإعلام',
                'admin_id' => $amalCity->id, 'dept_id' => $deptMedia->id,
                'email' => 'hos_media@system.com', 'user_name' => 'رئيس قسم الإعلام'
            ],
        ];

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
                    'total_capacity' => 10,
                    'status' => 'active',
                ]
            );
        }

        // أقسام "إدارة" (بدون رئيس قسم)
        $allAdmins = Administrative::all();
        foreach ($allAdmins as $admin) {
            Sections::firstOrCreate(
                ['name_location' => 'إدارة', 'administrative_id' => $admin->id],
                [
                    'department_id' => $deptAdmin->id,
                    'user_id' => null,
                    'total_capacity' => 5,
                    'status' => 'active',
                ]
            );
        }
    }
}