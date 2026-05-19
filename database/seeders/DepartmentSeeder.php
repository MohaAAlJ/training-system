<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\User as UserConstants;
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $password = '123';

        // Map department titles to their heads (matching the migration departments)
        $departmentHeads = [
            'صيدلة' => [
                'hod_email' => 'hod_pharma@system.com',
                'hod_name' => 'د. مدير الصيدلة'
            ],
            'تكنولوجيا المعلومات' => [
                'hod_email' => 'hod_it@system.com',
                'hod_name' => 'م. مدير الـ IT'
            ],
            'العلاقات العامة و الإعلام' => [
                'hod_email' => 'hod_media@system.com',
                'hod_name' => 'أ. مدير الإعلام'
            ],
            'إدارة' => [
                'hod_email' => 'hod_admin@system.com',
                'hod_name' => 'أ. مدير الإدارة العامة'
            ],
        ];

        // Assign heads to existing departments
        foreach ($departmentHeads as $deptTitle => $headData) {
            $dept = Department::where('name', $deptTitle)->first();
            if ($dept && $headData['hod_email']) {
                $hod = User::firstOrCreate(
                    ['email' => $headData['hod_email']],
                    [
                        'name' => $headData['hod_name'],
                        'user_name' => \Illuminate\Support\Str::slug($headData['hod_email'], '_'),
                        'password' => $password,
                        'role' => UserConstants::ROLE_DEPARTMENT,
                        'active' => true,
                    ]
                );
                $dept->update(['user_id' => $hod->id]);
            }
        }
    }
}
