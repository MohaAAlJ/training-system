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
        // 1. جلب البيانات الأكاديمية والمكانية المساعدة
        $govGaza = Governorate::where('name', 'like', '%غزة%')->first();
        $govKhan = Governorate::where('name', 'like', '%خانيونس%')->first();

        // جامعات
        $iug = Institution::where('name', 'like', '%الإسلامية%')->first();
        $azhar = Institution::where('name', 'like', '%الأزهر%')->first();
        $aqsa = Institution::where('name', 'like', '%الأقصى%')->first();

        // كليات وتخصصات (بحث تقريبي لضمان العمل)
        // IT
        $colIT = College::where('name', 'like', '%تكنولوجيا المعلومات%')->first();
        $majorDev = Major::where('name', 'like', '%برمجيات%')->first() ?? Major::first();
        
        // الصيدلة
        $colPharma = College::where('name', 'like', '%الصيدلة%')->first();
        $majorPharma = Major::where('name', 'like', '%الصيدلة%')->first();

        // الإعلام
        $colMedia = College::where('name', 'like', '%الإعلام%')->first();
        $majorMedia = Major::where('name', 'like', '%الصحافة%')->first();

        // المختبرات (العلوم الطبية)
        $colHealth = College::where('name', 'like', '%العلوم الطبية%')->orWhere('name', 'like', '%العلوم الصحية%')->first();
        $majorLab = Major::where('name', 'like', '%التحاليل الطبية%')->first();

        // إدارة
        $colAdmin = College::where('name', 'like', '%الإدارة%')->first();
        $majorAdmin = Major::where('name', 'like', '%إدارة الأعمال%')->first();


        $trainees = [
            // 1. متدرب IT (تدريب جامعي)
            [
                'national_id' => '400100100',
                'full_name' => 'أحمد محمد (طالب حاسوب)',
                'phone_number' => '0599100100',
                'dob' => '2002-05-15',
                'address' => 'الرمال، غزة',
                'gov_id' => $govGaza?->id,
                'inst_id' => $iug?->id,
                'col_id' => $colIT?->id,
                'major_id' => $majorDev?->id,
            ],
            // 2. خريجة صيدلة (مزاولة مهنة)
            [
                'national_id' => '400200200',
                'full_name' => 'سارة علي (خريجة صيدلة)',
                'phone_number' => '0599200200',
                'dob' => '2000-03-20',
                'address' => 'البلد، خانيونس',
                'gov_id' => $govKhan?->id,
                'inst_id' => $azhar?->id,
                'col_id' => $colPharma?->id,
                'major_id' => $majorPharma?->id,
            ],
            // 3. طالب إعلام
            [
                'national_id' => '400300300',
                'full_name' => 'محمود خالد (طالب إعلام)',
                'phone_number' => '0599300300',
                'dob' => '2003-01-01',
                'address' => 'المعسكر، دير البلح',
                'gov_id' => $govKhan?->id, // أو دير البلح
                'inst_id' => $aqsa?->id,
                'col_id' => $colMedia?->id,
                'major_id' => $majorMedia?->id,
            ],
            // 4. خريج مختبرات
            [
                'national_id' => '400400400',
                'full_name' => 'يوسف حسن (فني مختبر)',
                'phone_number' => '0599400400',
                'dob' => '1999-12-12',
                'address' => 'الشيخ رضوان',
                'gov_id' => $govGaza?->id,
                'inst_id' => $azhar?->id,
                'col_id' => $colHealth?->id,
                'major_id' => $majorLab?->id,
            ],
            // 5. طالبة إدارة
            [
                'national_id' => '400500500',
                'full_name' => 'منى سمير (سكرتارية)',
                'phone_number' => '0599500500',
                'dob' => '2004-07-07',
                'address' => 'تل الهوا',
                'gov_id' => $govGaza?->id,
                'inst_id' => $iug?->id,
                'col_id' => $colAdmin?->id,
                'major_id' => $majorAdmin?->id,
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
                    'training_hours' => 100,
                ]
            );
        }
    }
}