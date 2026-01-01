<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;

class InstitutionsSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [
            ['name' => 'الجامعة الإسلامية'],
            ['name' => 'جامعة الأزهر'],
            ['name' => 'جامعة الأقصى'],
            ['name' => 'جامعة القدس المفتوحة'],
            ['name' => 'جامعة فلسطين'],
            ['name' => 'جامعة غزة'],
            ['name' => 'جامعة الإسراء'],
            ['name' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['name' => 'كلية فلسطين التقنية - دير البلح'],
            ['name' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['name' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['name' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
            ['name' => 'كلية تنمية القدرات الجامعية - خانيونس'],
        ];

        foreach ($institutions as $inst) {
            Institution::firstOrCreate($inst);
        }
    }
}
