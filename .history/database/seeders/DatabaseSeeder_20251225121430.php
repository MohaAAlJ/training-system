<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. المستخدمين الأساسيين للنظام (Admin, MOH, GTM)
        $this->call(SystemUserSeeder::class);

        // 2. مشرفي الكليات (تحديث الكليات الموجودة بمشرفين جدد)
        $this->call(CollegeSupervisorSeeder::class);

        // 3. الهيكل الإداري (الدوائر والإدارات)
        $this->call(DepartmentSeeder::class);
        $this->call(AdministrativeSeeder::class);

        // 4. الأقسام (تعتمد على الدوائر والإدارات والمستخدمين)
        $this->call(SectionSeeder::class);

        // 5. المتدربين (الطلاب والخريجين)
        $this->call(TraineeSeeder::class);

        // 6. الطلبات (تعتمد على المتدربين والأقسام)
        $this->call(ApplicationSeeder::class);
    }
}
