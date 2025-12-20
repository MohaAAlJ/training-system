<?php

namespace Database\Seeders;

use App\Models\Departments;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            ['title' => 'الإدارة العامة للصيدلة', 'medical' => 1],
            ['title' => 'الإدارة العامة لتكنولوجيا المعلومات', 'medical' => 0],
            ['title' => 'الإدارة العامة للمختبرات', 'medical' => 1],
        ];

        foreach ($depts as $dept) {
            $user = User::factory()->create(['role' => 3]); // مدير عام التخصص

            Departments::create([
                'title' => $dept['title'],
                'is_medical' => $dept['medical'],
                'user_id' => $user->id,
            ]);
        }
    }
}
