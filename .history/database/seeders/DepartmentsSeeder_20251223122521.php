<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departments;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        $departments = [
            [
                'title' => 'الصيدلة',
                'is_medical' => true,
                'hod_email' => 'hod_pharma@system.com',
                'hod_name' => 'د. مدير الصيدلة'
            ],
            [
                'title' => 'تكنولوجيا المعلومات',
                'is_medical' => false,
                'hod_email' => 'hod_it@system.com',
                'hod_name' => 'م. مدير الـ IT'
            ],
            [
                'title' => 'الإعلام',
                'is_medical' => false,
                'hod_email' => 'hod_media@system.com',
                'hod_name' => 'أ. مدير الإعلام'
            ],
            [
                'title' => 'المختبرات',
                'is_medical' => true,
                'hod_email' => null, // بدون مدير حسب الصورة
                'hod_name' => null
            ],
            [
                'title' => 'الإدارة العامة',
                'is_medical' => false,
                'hod_email' => 'hod_admin@system.com',
                'hod_name' => 'أ. مدير الإدارة العامة'
            ],
        ];

        foreach ($departments as $deptData) {
            $hodId = null;

            if ($deptData['hod_email']) {
                $hod = User::firstOrCreate(
                    ['email' => $deptData['hod_email']],
                    [
                        'name' => $deptData['hod_name'],
                        'password' => $password,
                        'role' => Constans::ROLE_DEPARTMENT,
                        'status' => 'active',
                    ]
                );
                $hodId = $hod->id;
            }

            Departments::firstOrCreate(
                ['title' => $deptData['title']],
                [
                    'is_medical' => $deptData['is_medical'],
                    'user_id' => $hodId,
                ]
            );
        }
    }
}
