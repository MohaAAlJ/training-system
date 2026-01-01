<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorsSeeder extends Seeder
{
    private const MAJORS = [
        // Health & Medical
        ['ar' => 'طب بشري', 'en' => 'Medicine'],
        ['ar' => 'طب أسنان', 'en' => 'Dentistry'],
        ['ar' => 'صيدلة', 'en' => 'Pharmacy'],
        ['ar' => 'تمريض', 'en' => 'Nursing'],
        ['ar' => 'قبالة', 'en' => 'Midwifery'],
        ['ar' => 'تحاليل طبية', 'en' => 'Medical Laboratory Sciences'],
        ['ar' => 'علاج طبيعي', 'en' => 'Physiotherapy'],
        ['ar' => 'بصريات', 'en' => 'Optometry'],
        ['ar' => 'تصوير طبي', 'en' => 'Medical Imaging'],

        // Engineering
        ['ar' => 'هندسة مدنية', 'en' => 'Civil Engineering'],
        ['ar' => 'هندسة معمارية', 'en' => 'Architecture'],
        ['ar' => 'هندسة حاسوب', 'en' => 'Computer Engineering'],
        ['ar' => 'هندسة برمجيات', 'en' => 'Software Engineering'],
        ['ar' => 'هندسة ميكاترونيكس', 'en' => 'Mechatronics Engineering'],
        ['ar' => 'هندسة صناعية', 'en' => 'Industrial Engineering'],
        ['ar' => 'هندسة كهربائية', 'en' => 'Electrical Engineering'],

        // IT & Technology
        ['ar' => 'علم حاسوب', 'en' => 'Computer Science'],
        ['ar' => 'تكنولوجيا معلومات', 'en' => 'Information Technology'],
        ['ar' => 'أمن سيبراني', 'en' => 'Cybersecurity'],
        ['ar' => 'علم بيانات', 'en' => 'Data Science'],
        ['ar' => 'وسائط متعددة', 'en' => 'Multimedia'],
        ['ar' => 'تطوير ويب', 'en' => 'Web Development'],

        // Business & Law
        ['ar' => 'إدارة أعمال', 'en' => 'Business Administration'],
        ['ar' => 'محاسبة', 'en' => 'Accounting'],
        ['ar' => 'علوم مالية ومصرفية', 'en' => 'Banking and Finance'],
        ['ar' => 'تسويق رقمي', 'en' => 'Digital Marketing'],
        ['ar' => 'حقوق', 'en' => 'Law'],
        ['ar' => 'شريعة وقانون', 'en' => 'Sharia and Law'],

        // Humanities & Education
        ['ar' => 'لغة إنجليزية', 'en' => 'English Language'],
        ['ar' => 'لغة عربية', 'en' => 'Arabic Language'],
        ['ar' => 'صحافة وإعلام', 'en' => 'Journalism and Media'],
        ['ar' => 'خدمة اجتماعية', 'en' => 'Social Work'],
        ['ar' => 'تعليم أساسي', 'en' => 'Elementary Education'],
        ['ar' => 'إرشاد نفسي', 'en' => 'Psychological Counseling'],

        // Technical & Vocational (Colleges)
        ['ar' => 'فني إسعاف وطوارئ', 'en' => 'Emergency Medical Technician'],
        ['ar' => 'سكرتاريا طبية', 'en' => 'Medical Secretary'],
        ['ar' => 'تصميم أزياء', 'en' => 'Fashion Design'],
        ['ar' => 'تصميم داخلي (ديكور)', 'en' => 'Interior Design'],
        ['ar' => 'مساحة', 'en' => 'Surveying'],
    ];

    public function run(): void
    {
        foreach (self::MAJORS as $major) {
            Major::firstOrCreate(
                ['name->en' => $major['en']], // Check by English name
                [
                    'name' => $major, // Saves both AR and EN
                    'code' => strtoupper(substr($major['en'], 0, 3)) . rand(100, 999) // Generate a random code like MED101
                ]
            );
        }
    }
}
