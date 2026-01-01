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
        $departments = [
            ['title' => 'الصيدلة', 'is_medical' => true, 'hod_name' => 'د. صيدلي عام'],
            ['title' => 'التمريض', 'is_medical' => true, 'hod_name' => 'أ. حكيم عام'],
            ['title' => 'المختبرات', 'is_medical' => true, 'hod_name' => 'د. مخبري عام'],
            ['title' => 'الأشعة', 'is_medical' => true, 'hod_name' => 'د. إشعاعي عام'],
            ['title' => 'تكنولوجيا المعلومات', 'is_medical' => false, 'hod_name' => 'م. حاسوب عام'],
            ['title' => 'الموارد البشرية', 'is_medical' => false, 'hod_name' => 'أ. إداري موارد'],
            ['title' => 'العلاقات العامة', 'is_medical' => false, 'hod_name' => 'أ. علاقات عامة'],
        ];

        foreach ($departments as $deptData) {
            $hod = User::create([
                'name' => $deptData['hod_name'],
                'email' => 'hod_' . uniqid() . '@system.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_DEPARTMENT,
                'status' => 'active',
            ]);

            Departments::create([
                'title' => $deptData['title'],
                'is_medical' => $deptData['is_medical'],
                'hod' => $hod->id,
            ]);
        }
    }
}
