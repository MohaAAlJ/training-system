<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $data = [
            [1, 'الجامعة الإسلامية', 'The Islamic University of Gaza'],
            [2, 'جامعة الأزهر', 'Al-Azhar University'],
            [3, 'جامعة الأقصى', 'Al-Aqsa University'],
            [4, 'جامعة القدس المفتوحة', 'Al-Quds Open University'],
            [5, 'جامعة فلسطين', 'University of Palestine'],
            [6, 'جامعة غزة', 'Gaza University'],
            [7, 'جامعة الإسراء', 'Israa University'],
            [8, 'الكلية الجامعية للعلوم التطبيقية', 'University College of Applied Sciences'],
            [9, 'كلية فلسطين التقنية - دير البلح', 'Palestine Technical College - Deir Al-Balah'],
            [10, 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس', 'University College of Science and Technology'],
            [11, 'كلية نماء للعلوم والتكنولوجيا', 'Namaa College for Science and Technology'],
            [12, 'كلية مجتمع الأقصى للدراسات المتوسطة', 'Al-Aqsa Community College'],
            [13, 'كلية تنمية القدرات الجامعية - خانيونس', 'University College of Ability Development'],
        ];

        foreach ($data as $item) {
            DB::table('institutions')->insert([
                'id' => $item[0],
                'name' => json_encode(['ar' => $item[1], 'en' => $item[2]]),
                'created_at' => '2025-12-18 08:58:24',
                'updated_at' => '2025-12-18 08:58:24',
            ]);
        }
    }
    public function down(): void { Schema::dropIfExists('institutions'); }
};