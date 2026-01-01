<?php

namespace Database\Seeders;

use App\Models\Administratives;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrativesSeeder extends Seeder
{
    public function run(): void
    {
        // قائمة بأسماء مديريات واقعية
        $directorates = [
            'الإدارة العامة لتنمية القوى البشرية',
            'الإدارة العامة للمستشفيات',
            'الإدارة العامة للرعاية الأولية',
            'الإدارة العامة للهندسة والصيانة',
            'الإدارة العامة للشؤون الإدارية',
            'الإدارة العامة للصيدلة',
            'وحدة تكنولوجيا المعلومات',
            'وحدة العلاقات العامة والإعلام',
        ];

        foreach ($directorates as $title) {
            $manager = User::factory()->create([
                'name' => 'مدير ' . $title,
                'email' => 'manager_' . rand(100, 999) . '@moh.gov.ps',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMINISTRATIVE, 
                'status' => 'active',
            ]);

            Administratives::create([
                'title' => $title,
                'head_of_administrative' => $manager->name, 
                'user_id' => $manager->id, 
            ]);
        }
    }
}