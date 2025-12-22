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
            ['id' => 2, 'name' => 'جامعة الأزهر'],
            ['id' => 3, 'name' => 'جامعة الأقصى'],
            ['id' => 4, 'name' => 'جامعة القدس المفتوحة'],
            ['id' => 5, 'name' => 'جامعة فلسطين'],
            ['id' => 6, 'name' => 'جامعة غزة'],
            ['id' => 7, 'name' => 'جامعة الإسراء'],
            ['id' => 8, 'name' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['id' => 9, 'name' => 'كلية فلسطين التقنية - دير البلح'],
            ['id' => 10, 'name' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['id' => 11, 'name' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['id' => 12, 'name' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
            ['id' => 13, 'name' => 'كلية تنمية القدرات الجامعية - خانيونس'],
        ];

        foreach ($institutions as $inst) {
            DB::table('institutions')->insert([
                'id' => $inst['id'],
                'name' => $inst['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
