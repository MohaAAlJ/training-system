<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorsSeeder extends Seeder
{
    private const MAJORS = [
        // Health & Medical
        'طب بشري',
        'طب أسنان',
        'صيدلة',
        'تمريض',
        'قبالة',
        'تحاليل طبية',
        'علاج طبيعي',
        'بصريات',
        'تصوير طبي',

        // Engineering
        'هندسة مدنية',
        'هندسة معمارية',
        'هندسة حاسوب',
        'هندسة برمجيات',
        'هندسة ميكاترونيكس',
        'هندسة صناعية',
        'هندسة كهربائية',

        // IT & Technology
        'علم حاسوب',
        'تكنولوجيا معلومات',
        'أمن سيبراني',
        'علم بيانات',
        'وسائط متعددة',
        'تطوير ويب',

        // Business & Law
        'إدارة أعمال',
        'محاسبة',
        'علوم مالية ومصرفية',
        'تسويق رقمي',
        'حقوق',
        'شريعة وقانون',

        // Humanities & Education
        'لغة إنجليزية',
        'لغة عربية',
        'صحافة وإعلام',
        'خدمة اجتماعية',
        'تعليم أساسي',
        'إرشاد نفسي',

        // Technical & Vocational (Colleges)
        'فني إسعاف وطوارئ',
        'سكرتاريا طبية',
        'تصميم أزياء',
        'تصميم داخلي (ديكور)',
        'مساحة',
    ];

    public function run(): void
    {
        foreach (self::MAJORS as $name) {
            Major::firstOrCreate(
                ['name' => $name],
                [
                    'code' => strtoupper(substr(md5($name), 0, 3)) . rand(100, 999)
                ]
            );
        }
    }
}
