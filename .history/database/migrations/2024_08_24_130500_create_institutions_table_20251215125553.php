<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->softDeletes();
            $table->timestamps();
        });

        // Seed institutions data
        $institutions = [
            'الجامعة الإسلامية',
            'جامعة الأزهر',
            'جامعة الأقصى',
            'جامعة القدس المفتوحة',
            'جامعة فلسطين',
            'جامعة غزة',
            'جامعة الإسراء',
            'الكلية الجامعية للعلوم التطبيقية',
            'كلية فلسطين التقنية - دير البلح',
            'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس',
            'كلية نماء للعلوم والتكنولوجيا',
            'كلية مجتمع الأقصى للدراسات المتوسطة',
            'كلية تنمية القدرات الجامعية - خانيونس',
        ];

        foreach ($institutions as $instName) {
            \App\Models\Institution::create([
                'name' => ['ar' => $instName, 'en' => $instName]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
