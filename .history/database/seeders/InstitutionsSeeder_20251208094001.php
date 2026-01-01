<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء 5 مؤسسات عشوائية من القائمة الموجودة في الفاكتوري
        // يمكنك زيادة الرقم إذا أردت المزيد
        Institution::factory()->count(5)->create();
    }
}