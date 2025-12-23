<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainees;
use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use App\Models\Governorate;

class TraineesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب البيانات المساعدة (أماكن وجامعات)
        $govGaza = Governorate::where('name', 'like', '%غزة%')->first();
        $govKhan = Governorate::where('name', 'like', '%خانيونس%')->first();
        $govDeir = Governorate::where('name', 'like', '%دير البلح%')->first();

        // جامعات
        $iug = Institution::where('name', 'like', '%الإسلامية%')->first();
        $azhar = Institution::where('name', 'like', '%الأزهر%')->first();
        $aqsa = Institution::where('name', 'like', '%الأقصى%')->first();
        $ucas = Institution::where('name', 'like', '%الكلية الجامعية%')->first();

        // 2. جلب الكليات والتخصصات (لضمان الربط الصحيح)
        
        // IT (لأقسام البرمجيات والشبكات)
        $colIT = College::where('name', 'like', '%تكنولوجيا المعلومات%')->first();
        $majorSoft = Major::where('name', 'like', '%برمجيات%')->first() ?? Major::create(['name' => 'هندسة البرمجيات']);
        $majorNet = Major::where('name', 'like', '%شبكات%')->first() ?? Major::create(['name' => 'شبكات الحاسوب']);

        // Pharmacy (لأقسام الصيدلة)
        $colPharma = College::where('name', 'like', '%الصيدلة%')->first();
        $majorPharma = Major::where('name', 'like', '%الصيدلة%')->first() ?? Major::create(['name' => 'الصيدلة السريرية']);

        // Media (لقسم الإعلام)
        $colMedia = College::where('name', 'like', '%الإعلام%')->first();
        $majorMedia = Major::where('name', 'like', '%الصحافة%')->first() ?? Major::create(['name' => 'الصحافة والإعلام']);

        // Labs/Medical (للمختبرات)
        $colHealth = College::where('name', 'like', '%العلوم الطبية%')->orWhere('name', 'like', '%العلوم الصحية%')->first();
        $majorLab = Major::where('name', 'like', '%التحاليل الطبية%')->first() ?? Major::create(['name' => 'التحاليل الطبية']);

        $trainees = [
            // 1. طالبة IT - برمجيات (ستقدم لمركز الدير)
            [
                'national_id' => '400111111',
                'full_name' => 'مرح أحمد (طالبة برمجيات)',
                'phone_number' => '0599111111',
                'dob' => '2002-05-15',
                'address' => 'دير البلح',
                'gov_id' => $govDeir?->id,
                'inst_id' => $iug?->id,
                'col_id' => $colIT?->id,
                'major_id' => $majorSoft?->id,
            ],
            // 2. طالب IT - شبكات (سيقدم لمستشفى الأمل)
            [
                'national_id' => '400222222',
                'full_name' => 'خالد يوسف (طالب شبكات)',
                'phone_number' => '0599222222',
                'dob' => '2001-08-20',
                'address' => 'خانيونس',
                'gov_id' => $govKhan?->id,
                'inst_id' => $ucas?->id,
                'col_id' => $colIT?->id,
                'major_id' => $majorNet?->id,
            ],
            // 3. خريجة صيدلة - (ستقدم مزاولة مهنة)
            [
                'national_id' => '400333333',
                'full_name' => 'د. سارة علي (خريجة صيدلة)',
                'phone_number' => '0599333333',
                'dob' => '1999-03-10',
                'address' => 'الرمال، غزة',
                'gov_id' => $govGaza?->id,
                'inst_id' => $azhar?->id,
                'col_id' => $colPharma?->id,
                'major_id' => $majorPharma?->id,
            ],
            // 4. طالب إعلام - (سيقدم لمدينة الأمل)
            [
                'national_id' => '400444444',
                'full_name' => 'كريم محمود (صحافة)',
                'phone_number' => '0599444444',
                'dob' => '2003-01-01',
                'address' => 'خانيونس',
                'gov_id' => $govKhan?->id,
                'inst_id' => $aqsa?->id,
                'col_id' => $colMedia?->id,
                'major_id' => $majorMedia?->id,
            ],
            // 5. فني مختبرات - (سيقدم مزاولة مهنة)
            [
                'national_id' => '400555555',
                'full_name' => 'سامر حسن (فني مختبر)',
                'phone_number' => '0599555555',
                'dob' => '2000-12-12',
                'address' => 'رفح',
                'gov_id' => $govKhan?->id,
                'inst_id' => $azhar?->id,
                'col_id' => $colHealth?->id,
                'major_id' => $majorLab?->id,
            ],
        ];

        foreach ($trainees as $t) {
            Trainees::firstOrCreate(
                ['national_id' => $t['national_id']],
                [
                    'full_name' => $t['full_name'],
                    'phone_number' => $t['phone_number'],
                    'dob' => $t['dob'],
                    'address' => $t['address'],
                    'governorate_id' => $t['gov_id'],
                    'institution_id' => $t['inst_id'],
                    'college_id' => $t['col_id'],
                    'major_id' => $t['major_id'],
                    'training_hours' => 100, // ساعات افتراضية
                ]
            );
        }
    }
}