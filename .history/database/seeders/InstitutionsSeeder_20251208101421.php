<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    private const INSTITUTIONS = [
        'الجامعة الإسلامية بغزة',
        'جامعة الأزهر - غزة',
        'جامعة الأقصى',
        'جامعة القدس المفتوحة',
        'جامعة غزة',
        'جامعة فلسطين',
        'جامعة الإسراء',
        'الكلية الجامعية للعلوم التطبيقية',
        'كلية فلسطين التقنية - دير البلح',
        'كلية العودة الجامعية',
        'كلية تنمية القدرات',
    ];

    public function run(): void
    {
        foreach (self::INSTITUTIONS as $institution) {
            Institution::create(['name' => $institution]);
        }
    }
}