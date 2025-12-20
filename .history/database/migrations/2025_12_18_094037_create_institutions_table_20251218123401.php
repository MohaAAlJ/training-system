<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $institutions = [
            ['id' => 1, 'ar' => 'الجامعة الإسلامية', 'en' => 'الجامعة الإسلامية'],
            ['id' => 2, 'ar' => 'جامعة الأزهر', 'en' => 'جامعة الأزهر'],
            ['id' => 3, 'ar' => 'جامعة الأقصى', 'en' => 'جامعة الأقصى'],
            ['id' => 4, 'ar' => 'جامعة القدس المفتوحة', 'en' => 'جامعة القدس المفتوحة'],
            ['id' => 5, 'ar' => 'جامعة فلسطين', 'en' => 'جامعة فلسطين'],
            ['id' => 6, 'ar' => 'جامعة غزة', 'en' => 'جامعة غزة'],
            ['id' => 7, 'ar' => 'جامعة الإسراء', 'en' => 'جامعة الإسراء'],
            ['id' => 8, 'ar' => 'الكلية الجامعية للعلوم التطبيقية', 'en' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['id' => 9, 'ar' => 'كلية فلسطين التقنية - دير البلح', 'en' => 'كلية فلسطين التقنية - دير البلح'],
            ['id' => 10, 'ar' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس', 'en' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['id' => 11, 'ar' => 'كلية نماء للعلوم والتكنولوجيا', 'en' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['id' => 12, 'ar' => 'كلية مجتمع الأقصى للدراسات المتوسطة', 'en' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
            ['id' => 13, 'ar' => 'كلية تنمية القدرات الجامعية - خانيونس', 'en' => 'كلية تنمية القدرات الجامعية - خانيونس'],
        ];

        foreach ($institutions as $inst) {
            DB::table('institutions')->insert([
                'id' => $inst['id'],
                'name' => json_encode(['ar' => $inst['ar'], 'en' => $inst['en']]),
                'created_at' => '2025-12-17 04:45:30',
                'updated_at' => '2025-12-17 04:45:30',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
