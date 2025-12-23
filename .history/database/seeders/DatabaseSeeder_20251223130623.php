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
        $this->call(SystemUsersSeeder::class);

        // 2. مشرفي الكليات (تحديث الكليات الموجودة بمشرفين جدد)
        $this->call(CollegeSupervisorsSeeder::class);

        // 3. الهيكل الإداري (الدوائر والإدارات)
        $this->call(DepartmentsSeeder::class);
        $this->call(AdministrativesSeeder::class);

        // 4. الأقسام (تعتمد على الدوائر والإدارات والمستخدمين)
        $this->call(SectionsSeeder::class);

        // 5. المتدربين (الطلاب والخريجين)
        $this->call(TraineesSeeder::class);

        // 6. الطلبات (تعتمد على المتدربين والأقسام)
        $this->call(ApplicationsSeeder::class);
    }
}C