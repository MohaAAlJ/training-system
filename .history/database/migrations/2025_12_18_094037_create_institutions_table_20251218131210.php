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

        foreach ($institutions as $inst) {
    DB::table('institutions')->insert([
        'id' => $inst['id'],
        // إضافة JSON_UNESCAPED_UNICODE هنا لظهور العربي بشكل صريح
        'name' => json_encode(['ar' => $inst['ar'], 'en' => $inst['en']], JSON_UNESCAPED_UNICODE),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
    }
    public function down(): void { Schema::dropIfExists('institutions'); }
};