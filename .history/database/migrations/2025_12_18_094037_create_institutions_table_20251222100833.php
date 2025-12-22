<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

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
            DB::table('institutions')->insert($inst);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
