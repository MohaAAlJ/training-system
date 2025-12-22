<?php

use App\Models\College;
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
            ['id' => 1, 'institution_id' => 1, 'name' => 'كلية الطب'],
            ['id' => 2, 'institution_id' => 1, 'name' => 'كلية الهندسة'],
            ['id' => 3, 'institution_id' => 1, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['id' => 4, 'institution_id' => 1, 'name' => 'كلية العلوم'],
            ['id' => 5, 'institution_id' => 1, 'name' => 'كلية العلوم الصحية'],
            ['id' => 6, 'institution_id' => 1, 'name' => 'كلية التمريض'],
            ['id' => 7, 'institution_id' => 1, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['id' => 8, 'institution_id' => 1, 'name' => 'كلية الآداب'],
            ['id' => 9, 'institution_id' => 1, 'name' => 'كلية التربية'],
            ['id' => 10, 'institution_id' => 1, 'name' => 'كلية الشريعة والقانون'],
            ['id' => 11, 'institution_id' => 1, 'name' => 'كلية أصول الدين'],
            ['id' => 12, 'institution_id' => 8, 'name' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['id' => 13, 'institution_id' => 10, 'name' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['id' => 14, 'institution_id' => 2, 'name' => 'كلية الطب البشري'],
            ['id' => 15, 'institution_id' => 2, 'name' => 'كلية طب الأسنان'],
            ['id' => 16, 'institution_id' => 2, 'name' => 'كلية الصيدلة'],
            ['id' => 17, 'institution_id' => 2, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['id' => 18, 'institution_id' => 2, 'name' => 'كلية العلوم'],
            ['id' => 19, 'institution_id' => 2, 'name' => 'كلية العلوم الطبية التطبيقية'],
            ['id' => 20, 'institution_id' => 2, 'name' => 'كلية الزراعة والطب البيطري'],
            ['id' => 21, 'institution_id' => 2, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['id' => 22, 'institution_id' => 2, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['id' => 23, 'institution_id' => 2, 'name' => 'كلية الحقوق'],
            ['id' => 24, 'institution_id' => 2, 'name' => 'كلية التربية'],
            ['id' => 25, 'institution_id' => 2, 'name' => 'كلية الشريعة'],
            ['id' => 26, 'institution_id' => 3, 'name' => 'كلية الإعلام'],
            ['id' => 27, 'institution_id' => 3, 'name' => 'كلية الفنون الجميلة'],
            ['id' => 28, 'institution_id' => 3, 'name' => 'كلية التربية البدنية والرياضة'],
            ['id' => 29, 'institution_id' => 3, 'name' => 'كلية الحاسبات وتكنولوجيا المعلومات'],
            ['id' => 30, 'institution_id' => 3, 'name' => 'كلية العلوم الطبية'],
            ['id' => 31, 'institution_id' => 3, 'name' => 'كلية العلوم'],
            ['id' => 32, 'institution_id' => 3, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['id' => 33, 'institution_id' => 3, 'name' => 'كلية التربية'],
            ['id' => 34, 'institution_id' => 3, 'name' => 'كلية الإدارة والتمويل'],
            ['id' => 35, 'institution_id' => 3, 'name' => 'كلية العلوم الإسلامية'],
            ['id' => 36, 'institution_id' => 4, 'name' => 'كلية التكنولوجيا والعلوم التطبيقية'],
            ['id' => 37, 'institution_id' => 4, 'name' => 'كلية التنمية الاجتماعية والأسرية'],
            ['id' => 38, 'institution_id' => 4, 'name' => 'كلية العلوم الإدارية والاقتصادية'],
            ['id' => 39, 'institution_id' => 4, 'name' => 'كلية الآداب'],
            ['id' => 40, 'institution_id' => 4, 'name' => 'كلية العلوم التربوية'],
            ['id' => 41, 'institution_id' => 4, 'name' => 'كلية الزراعة'],
            ['id' => 42, 'institution_id' => 6, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['id' => 43, 'institution_id' => 6, 'name' => 'كلية الحقوق'],
            ['id' => 44, 'institution_id' => 6, 'name' => 'كلية علوم الحاسوب وتكنولوجيا المعلومات'],
            ['id' => 45, 'institution_id' => 6, 'name' => 'كلية علوم الاتصال واللغات'],
            ['id' => 46, 'institution_id' => 6, 'name' => 'كلية التربية'],
            ['id' => 47, 'institution_id' => 6, 'name' => 'كلية العلوم الطبية'],
            ['id' => 48, 'institution_id' => 7, 'name' => 'كلية العلوم الطبية'],
            ['id' => 49, 'institution_id' => 7, 'name' => 'كلية القانون'],
            ['id' => 50, 'institution_id' => 7, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['id' => 51, 'institution_id' => 7, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['id' => 52, 'institution_id' => 7, 'name' => 'كلية العلوم الإنسانية'],
            ['id' => 53, 'institution_id' => 5, 'name' => 'كلية طب الأسنان'],
            ['id' => 54, 'institution_id' => 5, 'name' => 'كلية الصيدلة والتكنولوجيا الحيوية'],
            ['id' => 55, 'institution_id' => 5, 'name' => 'كلية الهندسة التطبيقية والتخطيط العمراني'],
            ['id' => 56, 'institution_id' => 5, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['id' => 57, 'institution_id' => 5, 'name' => 'كلية القانون والممارسة القضائية'],
            ['id' => 58, 'institution_id' => 5, 'name' => 'كلية إدارة المال والأعمال'],
            ['id' => 59, 'institution_id' => 5, 'name' => 'كلية الإعلام والاتصال'],
            ['id' => 60, 'institution_id' => 5, 'name' => 'كلية التربية'],
            ['id' => 61, 'institution_id' => 11, 'name' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['id' => 62, 'institution_id' => 13, 'name' => 'كلية تنمية القدرات الجامعية - خانيونس'],
            ['id' => 63, 'institution_id' => 9, 'name' => 'كلية فلسطين التقنية - دير البلح'],
            ['id' => 64, 'institution_id' => 12, 'name' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
        ];

        foreach ($colleges as $col) {
            // DB::table('colleges')->insert([
            //     'id' => $col['id'],
            //     'institution_id' => $col['institution_id'],
            //     'name' => $col['name'],
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
            College::create([$col]);
                
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
