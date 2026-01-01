<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        $colleges = [
            ['id' => 1, 'institution_id ' => 1, 'name' => 'كلية الطب'],
            ['id' => 2, 'inst_id' => 1, 'name' => 'كلية الهندسة'],
            ['id' => 3, 'inst_id' => 1, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['id' => 4, 'inst_id' => 1, 'name' => 'كلية العلوم'],
            ['id' => 5, 'inst_id' => 1, 'name' => 'كلية العلوم الصحية'],
            ['id' => 6, 'inst_id' => 1, 'name' => 'كلية التمريض'],
            ['id' => 7, 'inst_id' => 1, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['id' => 8, 'inst_id' => 1, 'name' => 'كلية الآداب'],
            ['id' => 9, 'inst_id' => 1, 'name' => 'كلية التربية'],
            ['id' => 10, 'inst_id' => 1, 'name' => 'كلية الشريعة والقانون'],
            ['id' => 11, 'inst_id' => 1, 'name' => 'كلية أصول الدين'],
            ['id' => 12, 'inst_id' => 8, 'name' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['id' => 13, 'inst_id' => 10, 'name' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['id' => 14, 'inst_id' => 2, 'name' => 'كلية الطب البشري'],
            ['id' => 15, 'inst_id' => 2, 'name' => 'كلية طب الأسنان'],
            ['id' => 16, 'inst_id' => 2, 'name' => 'كلية الصيدلة'],
            ['id' => 17, 'inst_id' => 2, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['id' => 18, 'inst_id' => 2, 'name' => 'كلية العلوم'],
            ['id' => 19, 'inst_id' => 2, 'name' => 'كلية العلوم الطبية التطبيقية'],
            ['id' => 20, 'inst_id' => 2, 'name' => 'كلية الزراعة والطب البيطري'],
            ['id' => 21, 'inst_id' => 2, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['id' => 22, 'inst_id' => 2, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['id' => 23, 'inst_id' => 2, 'name' => 'كلية الحقوق'],
            ['id' => 24, 'inst_id' => 2, 'name' => 'كلية التربية'],
            ['id' => 25, 'inst_id' => 2, 'name' => 'كلية الشريعة'],
            ['id' => 26, 'inst_id' => 3, 'name' => 'كلية الإعلام'],
            ['id' => 27, 'inst_id' => 3, 'name' => 'كلية الفنون الجميلة'],
            ['id' => 28, 'inst_id' => 3, 'name' => 'كلية التربية البدنية والرياضة'],
            ['id' => 29, 'inst_id' => 3, 'name' => 'كلية الحاسبات وتكنولوجيا المعلومات'],
            ['id' => 30, 'inst_id' => 3, 'name' => 'كلية العلوم الطبية'],
            ['id' => 31, 'inst_id' => 3, 'name' => 'كلية العلوم'],
            ['id' => 32, 'inst_id' => 3, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['id' => 33, 'inst_id' => 3, 'name' => 'كلية التربية'],
            ['id' => 34, 'inst_id' => 3, 'name' => 'كلية الإدارة والتمويل'],
            ['id' => 35, 'inst_id' => 3, 'name' => 'كلية العلوم الإسلامية'],
            ['id' => 36, 'inst_id' => 4, 'name' => 'كلية التكنولوجيا والعلوم التطبيقية'],
            ['id' => 37, 'inst_id' => 4, 'name' => 'كلية التنمية الاجتماعية والأسرية'],
            ['id' => 38, 'inst_id' => 4, 'name' => 'كلية العلوم الإدارية والاقتصادية'],
            ['id' => 39, 'inst_id' => 4, 'name' => 'كلية الآداب'],
            ['id' => 40, 'inst_id' => 4, 'name' => 'كلية العلوم التربوية'],
            ['id' => 41, 'inst_id' => 4, 'name' => 'كلية الزراعة'],
            ['id' => 42, 'inst_id' => 6, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['id' => 43, 'inst_id' => 6, 'name' => 'كلية الحقوق'],
            ['id' => 44, 'inst_id' => 6, 'name' => 'كلية علوم الحاسوب وتكنولوجيا المعلومات'],
            ['id' => 45, 'inst_id' => 6, 'name' => 'كلية علوم الاتصال واللغات'],
            ['id' => 46, 'inst_id' => 6, 'name' => 'كلية التربية'],
            ['id' => 47, 'inst_id' => 6, 'name' => 'كلية العلوم الطبية'],
            ['id' => 48, 'inst_id' => 7, 'name' => 'كلية العلوم الطبية'],
            ['id' => 49, 'inst_id' => 7, 'name' => 'كلية القانون'],
            ['id' => 50, 'inst_id' => 7, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['id' => 51, 'inst_id' => 7, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['id' => 52, 'inst_id' => 7, 'name' => 'كلية العلوم الإنسانية'],
            ['id' => 53, 'inst_id' => 5, 'name' => 'كلية طب الأسنان'],
            ['id' => 54, 'inst_id' => 5, 'name' => 'كلية الصيدلة والتكنولوجيا الحيوية'],
            ['id' => 55, 'inst_id' => 5, 'name' => 'كلية الهندسة التطبيقية والتخطيط العمراني'],
            ['id' => 56, 'inst_id' => 5, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['id' => 57, 'inst_id' => 5, 'name' => 'كلية القانون والممارسة القضائية'],
            ['id' => 58, 'inst_id' => 5, 'name' => 'كلية إدارة المال والأعمال'],
            ['id' => 59, 'inst_id' => 5, 'name' => 'كلية الإعلام والاتصال'],
            ['id' => 60, 'inst_id' => 5, 'name' => 'كلية التربية'],
            ['id' => 61, 'inst_id' => 11, 'name' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['id' => 62, 'inst_id' => 13, 'name' => 'كلية تنمية القدرات الجامعية - خانيونس'],
            ['id' => 63, 'inst_id' => 9, 'name' => 'كلية فلسطين التقنية - دير البلح'],
            ['id' => 64, 'inst_id' => 12, 'name' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
        ];

        foreach ($colleges as $col) {
            DB::table('colleges')->insert([
                'id' => $col['id'],
                'institution_id' => $col['inst_id'],
                'name' => $col['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
