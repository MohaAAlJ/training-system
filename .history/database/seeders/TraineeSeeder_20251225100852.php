<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use App\Models\Governorate;

class TraineesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب المحافظات (بحث دقيق)
        $govGaza = Governorate::where('name', 'like', '%غزة%')->where('name', 'not like', '%شمال%')->first();
        $govKhan = Governorate::where('name', 'like', '%خانيونس%')->first();
        $govDeir = Governorate::where('name', 'like', '%دير البلح%')->first();

        // 2. دوال مساعدة لجلب البيانات المتوافقة
        $getUniData = function ($uniName, $colName, $majorName) {
            $uni = Institution::where('name', 'like', "%$uniName%")->first();
            if (!$uni) return [null, null, null];

            $col = College::where('institution_id', $uni->id)
                ->where('name', 'like', "%$colName%")
                ->first();
            if (!$col) return [$uni->id, null, null];

            $major = $col->majors()->where('name', 'like', "%$majorName%")->first();
            if (!$major) {
                // محاولة البحث عن التخصص بشكل عام إذا لم يكن مرتبطاً بالكلية في جدول الوسيط
                $major = Major::where('name', 'like', "%$majorName%")->first();
            }

            return [$uni->id, $col->id, $major?->id];
        };

        // 3. تعريف المتدربين مع ضمان توافق البيانات
        $traineesData = [
            [
                'national_id' => '400100100',
                'full_name' => 'أحمد محمد (طالب حاسوب)',
                'phone_number' => '0599100100',
                'dob' => '2002-05-15',
                'address' => 'الرمال، غزة',
                'gov_id' => $govGaza?->id,
                'uni_params' => ['الإسلامية', 'تكنولوجيا المعلومات', 'برمجيات']
            ],
            [
                'national_id' => '400200200',
                'full_name' => 'سارة علي (خريجة صيدلة)',
                'phone_number' => '0599200200',
                'dob' => '2000-03-20',
                'address' => 'البلد، خانيونس',
                'gov_id' => $govKhan?->id,
                'uni_params' => ['الأزهر', 'الصيدلة', 'الصيدلة']
            ],
            [
                'national_id' => '400300300',
                'full_name' => 'محمود خالد (طالب إعلام)',
                'phone_number' => '0599300300',
                'dob' => '2003-01-01',
                'address' => 'المعسكر، دير البلح',
                'gov_id' => $govDeir?->id,
                'uni_params' => ['الأقصى', 'الإعلام', 'الصحافة']
            ],
            [
                'national_id' => '400400400',
                'full_name' => 'يوسف حسن (فني مختبر)',
                'phone_number' => '0599400400',
                'dob' => '1999-12-12',
                'address' => 'الشيخ رضوان',
                'gov_id' => $govGaza?->id,
                'uni_params' => ['الأزهر', 'العلوم الطبية', 'التحاليل الطبية']
            ],
            [
                'national_id' => '400500500',
                'full_name' => 'منى سمير (سكرتارية)',
                'phone_number' => '0599500500',
                'dob' => '2004-07-07',
                'address' => 'تل الهوا',
                'gov_id' => $govGaza?->id,
                'uni_params' => ['الإسلامية', 'الاقتصاد', 'إدارة الأعمال']
            ],
        ];

        foreach ($traineesData as $t) {
            [$inst_id, $col_id, $major_id] = $getUniData(...$t['uni_params']);

            Trainee::firstOrCreate(
                ['national_id' => $t['national_id']],
                [
                    'full_name' => $t['full_name'],
                    'phone_number' => $t['phone_number'],
                    'dob' => $t['dob'],
                    'address' => $t['address'],
                    'governorate_id' => $t['gov_id'],
                    'institution_id' => $inst_id,
                    'college_id' => $col_id,
                    'major_id' => $major_id,
                    'training_hours' => 100,
                ]
            );
        }
    }
}
