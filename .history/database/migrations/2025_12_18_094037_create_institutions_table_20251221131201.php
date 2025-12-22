<?php

use App\Models\Institution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $institutions = [
            ['id' => 1, 'ar' => 'الجامعة الإسلامية', 'en' => 'The Islamic University of Gaza'],
            ['id' => 2, 'ar' => 'جامعة الأزهر', 'en' => 'Al-Azhar University'],
            ['id' => 3, 'ar' => 'جامعة الأقصى', 'en' => 'Al-Aqsa University'],
            ['id' => 4, 'ar' => 'جامعة القدس المفتوحة', 'en' => 'Al-Quds Open University'],
            ['id' => 5, 'ar' => 'جامعة فلسطين', 'en' => 'University of Palestine'],
            ['id' => 6, 'ar' => 'جامعة غزة', 'en' => 'Gaza University'],
            ['id' => 7, 'ar' => 'جامعة الإسراء', 'en' => 'Israa University'],
            ['id' => 8, 'ar' => 'الكلية الجامعية للعلوم التطبيقية', 'en' => 'University College of Applied Sciences'],
            ['id' => 9, 'ar' => 'كلية فلسطين التقنية - دير البلح', 'en' => 'Palestine Technical College - Deir Al-Balah'],
            ['id' => 10, 'ar' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس', 'en' => 'University College of Science and Technology'],
            ['id' => 11, 'ar' => 'كلية نماء للعلوم والتكنولوجيا', 'en' => 'Namaa College for Science and Technology'],
            ['id' => 12, 'ar' => 'كلية مجتمع الأقصى للدراسات المتوسطة', 'en' => 'Al-Aqsa Community College'],
            ['id' => 13, 'ar' => 'كلية تنمية القدرات الجامعية - خانيونس', 'en' => 'University College of Ability Development'],
        ];

        foreach ($institutions as $inst) {
            Institution::insert([
                'id' => $inst['id'],
                'name' => json_encode(['ar' => $inst['ar'], 'en' => $inst['en']], JSON_UNESCAPED_UNICODE),
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
