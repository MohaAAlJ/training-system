<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تشغيل السيدرز الأساسية للنظام (Admin, MOH, GTM)
        $this->call(SystemUsersSeeder::class);

        // 2. تشغيل سيدر الكليات (5 حسابات لـ 5 كليات مختلفة)
        $this->call(CollegeSupervisorsSeeder::class);

        // 3. بناء الهيكل الإداري (حسب الصورة المرفقة)
        // الترتيب مهم: دوائر -> إدارات -> أقسام
        $this->call(DepartmentsSeeder::class);
        $this->call(AdministrativesSeeder::class);
        $this->call(SectionsSeeder::class);
    }
}