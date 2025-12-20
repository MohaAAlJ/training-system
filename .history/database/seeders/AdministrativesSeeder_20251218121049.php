<?php

namespace Database\Seeders;

use App\Models\Administratives;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdministrativesSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = ['مستشفى الأمل', 'مجمع الشفاء الطبي', 'مستشفى ناصر', 'مدينة النور'];

        foreach ($facilities as $facility) {
            $user = User::factory()->create(['role' => 2]); // إنشاء مدير لكل منشأة

            Administratives::create([
                'title' => $facility,
                'user_id' => $user->id,
            ]);
        }
    }
}
