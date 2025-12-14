<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    // 1. أولاً: إدخال الهيكل الأكاديمي الحقيقي (جامعات - كليات - تخصصات)
    // هذا السيدر هو الذي يحتوي المصفوفة الضخمة التي كتبناها سابقاً
    $this->call(RealDataSeeder::class); 

    // 2. ثانياً: إدخال الهيكل الإداري (الوزارة والمديريات)
    $this->call([
        UserSeeder::class,         // المستخدمين
        AdministrativesSeeder::class, // المديريات
        DepartmentsSeeder::class,     // الأقسام
    ]);

    // 3. ثالثاً: إنشاء المتدربين
    // الآن الفاكتوري سيجد بيانات حقيقية ليختار منها
    $this->call(TraineesSeeder::class);
    
    // 4. أخيراً: الطلبات
    $this->call(ApplicationsSeeder::class);
}
}