<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\College;
use App\Models\Institution;

use Illuminate\Support\Facades\Hash;

class CollegeSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        // قائمة لتحديد مشرفين لكليات محددة في جامعات محددة للتنويع
        $targets = [
            [
                'uni_name' => 'الجامعة الإسلامية',
                'col_name' => 'كلية الهندسة',
                'user_name' => 'مشرف هندسة الإسلامية',
                'email' => 'sup_eng_iug@system.com'
            ],
            [
                'uni_name' => 'جامعة الأزهر',
                'col_name' => 'كلية الصيدلة',
                'user_name' => 'مشرف صيدلة الأزهر',
                'email' => 'sup_pharm_azhar@system.com'
            ],
            [
                'uni_name' => 'جامعة الأقصى',
                'col_name' => 'كلية الإعلام',
                'user_name' => 'مشرف إعلام الأقصى',
                'email' => 'sup_media_aqsa@system.com'
            ],
            [
                'uni_name' => 'الكلية الجامعية للعلوم التطبيقية', // Institution ID 8
                'col_name' => 'الكلية الجامعية للعلوم التطبيقية', // في بياناتك الكلية تحمل نفس اسم الجامعة
                'user_name' => 'مشرف الكلية الجامعية',
                'email' => 'sup_ucas@system.com'
            ],
            [
                'uni_name' => 'كلية فلسطين التقنية - دير البلح', // Institution ID 9
                'col_name' => 'كلية فلسطين التقنية - دير البلح', // في بياناتك الكلية تحمل نفس اسم الجامعة
                'user_name' => 'مشرف تقنية دير البلح',
                'email' => 'sup_tech_deir@system.com'
            ],
        ];

        foreach ($targets as $target) {
            // 1. البحث عن الجامعة
            $institution = Institution::where('name', $target['uni_name'])->first();

            if (!$institution) {
                continue; // تخطي إذا لم توجد الجامعة
            }

            // 2. البحث عن الكلية داخل هذه الجامعة
            $college = College::where('institution_id', $institution->id)
                ->where('name', $target['col_name'])
                ->first();

            if ($college) {
                // 3. إنشاء حساب المشرف
                $user = User::firstOrCreate(
                    ['email' => $target['email']],
                    [
                        'name' => $target['user_name'], // Note: original code used user_name as name
                        'user_name' => \Illuminate\Support\Str::slug($target['user_name'], '_'),
                        'password' => $password,
                        'role' => User::ROLE_COLLEGE,
                        'status' => 'active',
                    ]
                );

                // 4. ربط المشرف بالكلية
                $college->update(['user_id' => $user->id]);
            }
        }
    }
}
