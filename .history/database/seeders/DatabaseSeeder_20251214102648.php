<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. المستخدمين والصلاحيات
            UserSeeder::class,

            // 2. البيانات الأكاديمية الحقيقية (الجامعات - الكليات - التخصصات)
            RealDataSeeder::class,

            // 3. اله
            DepartmentsSeeder::class,

            // 4. المتدربين وطلباتهم
            TraineesSeeder::class,
            ApplicationsSeeder::class,
        ]);
    }
}