<?php

namespace Database\Seeders;

use App\Models\Administratives;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrativesSeeder extends Seeder
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
            $manager = User::factory()->create([
                'name' => 'مدير ' . $directorateData['title'],
                'email' => 'manager_' . rand(100, 999) . '@moh.gov.ps',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_ADMINISTRATIVE,
                'status' => 'active',
            ]);

            $data = [
                'title' => $directorateData['title'],
                'user_id' => $manager->id,
                'is_medical' => $directorateData['is_medical'],
            ];

            // If medical, create a separate medical head
            if ($directorateData['is_medical']) {
                $medicalHead = User::factory()->create([
                    'name' => 'رئيس الإدارة الطبية ' . $directorateData['title'],
                    'email' => 'medical_head_' . rand(100, 999) . '@moh.gov.ps',
                    'password' => Hash::make('password'),
                    'role' => Constans::ROLE_ADMINISTRATIVE,
                    'status' => 'active',
                ]);
                $data['medical_head_user_id'] = $medicalHead->id;
            }

            Administratives::create($data);
        }
    }
}
