<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    private const INSTITUTIONS = [
        // الجامعات
        'الجامعة الإسلامية بغزة',
        'جامعة الأزهر - غزة',
        'جامعة الأقصى',
        'جامعة القدس المفتوحة - فروع غزة',
        'جامعة غزة',
        'جامعة فلسطين',
        'جامعة الإسراء',

        // الكليات الجامعية
        'الكلية الجامعية للعلوم التطبيقية',
        'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس',
        'كلية فلسطين التقنية - دير البلح',
        'كلية العودة الجامعية',
        'كلية تنمية القدرات الجامعية',
        'كلية الصحابة الجامعية',
        'كلية الزيتونة الجامعية',
        'كلية الرباط الجامعية',
        'كلية نماء للعلوم والتكنولوجيا',

        // كليات المجتمع
        'كلية مجتمع الأقصى للدراسات المتوسطة',
    ];

    public function run(): void
    {
        foreach (self::INSTITUTIONS as $name) {
            Institution::create(['name' => $name]);
        }
    }
}
