<?php

namespace Database\Seeders;

use App\Models\Administrative;
use App\Models\Departments;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $directorates = [
            ['title' => 'الإدارة العامة لتنمية القوى البشرية', 'is_medical' => false],
            ['title' => 'الإدارة العامة للمستشفيات', 'is_medical' => true],
            ['title' => 'الإدارة العامة للرعاية الأولية', 'is_medical' => true],
            ['title' => 'الإدارة العامة للهندسة والصيانة', 'is_medical' => false],
            ['title' => 'الإدارة العامة للشؤون الإدارية', 'is_medical' => false],
            ['title' => 'الإدارة العامة للصيدلة', 'is_medical' => true],
            ['title' => 'وحدة تكنولوجيا المعلومات', 'is_medical' => false],
            ['title' => 'وحدة العلاقات العامة والإعلام', 'is_medical' => false],
        ];

        foreach ($directorates as $directorateData) {
            // Ensure unique email addresses to avoid duplicate key errors during seeding
            $uniqueSuffix = uniqid();
            $manager = User::factory()->create([
                'name' => 'مدير ' . $directorateData['title'],
                'email' => 'manager_' . $uniqueSuffix . '@moh.gov.ps',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_DEPARTMENT,
                'status' => 'active',
            ]);

            $data = [
                'title' => $directorateData['title'],
                'user_id' => $manager->id,
            ];

            Departments::create($data);
        }
    }
}
