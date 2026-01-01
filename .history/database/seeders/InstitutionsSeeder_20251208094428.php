<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    public function run(): void
    {
        // قائمة الجامعات الحقيقية في غزة
        $universities = [
            ['ar' => 'الجامعة الإسلامية بغزة', 'en' => 'Islamic University of Gaza'],
            ['ar' => 'جامعة الأزهر - غزة', 'en' => 'Al-Azhar University - Gaza'],
            ['ar' => 'جامعة الأقصى', 'en' => 'Al-Aqsa University'],
            ['ar' => 'جامعة القدس المفتوحة', 'en' => 'Al-Quds Open University'],
            ['ar' => 'جامعة غزة', 'en' => 'Gaza University'],
            ['ar' => 'جامعة فلسطين', 'en' => 'University of Palestine'],
            ['ar' => 'جامعة الإسراء', 'en' => 'Israa University'],
            ['ar' => 'الكلية الجامعية للعلوم التطبيقية', 'en' => 'University College of Applied Sciences'],
            ['ar' => 'كلية فلسطين التقنية - دير البلح', 'en' => 'Palestine Technical College'],
            ['ar' => 'كلية العودة الجامعية', 'en' => 'Al-Awda University College'],
            ['ar' => 'كلية تنمية القدرات', 'en' => 'Ability Development College'],
        ];

        foreach ($universities as $uni) {
            // نقوم بإنشاء السجل
            // لاراڤيل سيقوم بتحويل المصفوفة ['ar' => ..., 'en' => ...] إلى JSON تلقائياً
            Institution::create([
                'name' => $uni
            ]);
        }
    }
}