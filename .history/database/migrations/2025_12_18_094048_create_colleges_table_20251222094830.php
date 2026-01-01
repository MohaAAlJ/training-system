<?php

use App\Models\College;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\In;
use App\Models\Institution;
use App

return new class extends Migration {
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(Institution::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        $colleges = [
            ['institution_id' => 1, 'name' => 'كلية الطب'],
            ['institution_id' => 1, 'name' => 'كلية الهندسة'],
            ['institution_id' => 1, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['institution_id' => 1, 'name' => 'كلية العلوم'],
            ['institution_id' => 1, 'name' => 'كلية العلوم الصحية'],
            ['institution_id' => 1, 'name' => 'كلية التمريض'],
            ['institution_id' => 1, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['institution_id' => 1, 'name' => 'كلية الآداب'],
            ['institution_id' => 1, 'name' => 'كلية التربية'],
            ['institution_id' => 1, 'name' => 'كلية الشريعة والقانون'],
            ['institution_id' => 1, 'name' => 'كلية أصول الدين'],
            ['institution_id' => 8, 'name' => 'الكلية الجامعية للعلوم التطبيقية'],
            ['institution_id' => 10, 'name' => 'الكلية الجامعية للعلوم والتكنولوجيا - خانيونس'],
            ['institution_id' => 2, 'name' => 'كلية الطب البشري'],
            ['institution_id' => 2, 'name' => 'كلية طب الأسنان'],
            ['institution_id' => 2, 'name' => 'كلية الصيدلة'],
            ['institution_id' => 2, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['institution_id' => 2, 'name' => 'كلية العلوم'],
            ['institution_id' => 2, 'name' => 'كلية العلوم الطبية التطبيقية'],
            ['institution_id' => 2, 'name' => 'كلية الزراعة والطب البيطري'],
            ['institution_id' => 2, 'name' => 'كلية الاقتصاد والعلوم الإدارية'],
            ['institution_id' => 2, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['institution_id' => 2, 'name' => 'كلية الحقوق'],
            ['institution_id' => 2, 'name' => 'كلية التربية'],
            ['institution_id' => 2, 'name' => 'كلية الشريعة'],
            ['institution_id' => 3, 'name' => 'كلية الإعلام'],
            ['institution_id' => 3, 'name' => 'كلية الفنون الجميلة'],
            ['institution_id' => 3, 'name' => 'كلية التربية البدنية والرياضة'],
            ['institution_id' => 3, 'name' => 'كلية الحاسبات وتكنولوجيا المعلومات'],
            ['institution_id' => 3, 'name' => 'كلية العلوم الطبية'],
            ['institution_id' => 3, 'name' => 'كلية العلوم'],
            ['institution_id' => 3, 'name' => 'كلية الآداب والعلوم الإنسانية'],
            ['institution_id' => 3, 'name' => 'كلية التربية'],
            ['institution_id' => 3, 'name' => 'كلية الإدارة والتمويل'],
            ['institution_id' => 3, 'name' => 'كلية العلوم الإسلامية'],
            ['institution_id' => 4, 'name' => 'كلية التكنولوجيا والعلوم التطبيقية'],
            ['institution_id' => 4, 'name' => 'كلية التنمية الاجتماعية والأسرية'],
            ['institution_id' => 4, 'name' => 'كلية العلوم الإدارية والاقتصادية'],
            ['institution_id' => 4, 'name' => 'كلية الآداب'],
            ['institution_id' => 4, 'name' => 'كلية العلوم التربوية'],
            ['institution_id' => 4, 'name' => 'كلية الزراعة'],
            ['institution_id' => 6, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['institution_id' => 6, 'name' => 'كلية الحقوق'],
            ['institution_id' => 6, 'name' => 'كلية علوم الحاسوب وتكنولوجيا المعلومات'],
            ['institution_id' => 6, 'name' => 'كلية علوم الاتصال واللغات'],
            ['institution_id' => 6, 'name' => 'كلية التربية'],
            ['institution_id' => 6, 'name' => 'كلية العلوم الطبية'],
            ['institution_id' => 7, 'name' => 'كلية العلوم الطبية'],
            ['institution_id' => 7, 'name' => 'كلية القانون'],
            ['institution_id' => 7, 'name' => 'كلية العلوم الإدارية والمالية'],
            ['institution_id' => 7, 'name' => 'كلية الهندسة وتكنولوجيا المعلومات'],
            ['institution_id' => 7, 'name' => 'كلية العلوم الإنسانية'],
            ['institution_id' => 5, 'name' => 'كلية طب الأسنان'],
            ['institution_id' => 5, 'name' => 'كلية الصيدلة والتكنولوجيا الحيوية'],
            ['institution_id' => 5, 'name' => 'كلية الهندسة التطبيقية والتخطيط العمراني'],
            ['institution_id' => 5, 'name' => 'كلية تكنولوجيا المعلومات'],
            ['institution_id' => 5, 'name' => 'كلية القانون والممارسة القضائية'],
            ['institution_id' => 5, 'name' => 'كلية إدارة المال والأعمال'],
            ['institution_id' => 5, 'name' => 'كلية الإعلام والاتصال'],
            ['institution_id' => 5, 'name' => 'كلية التربية'],
            ['institution_id' => 11, 'name' => 'كلية نماء للعلوم والتكنولوجيا'],
            ['institution_id' => 13, 'name' => 'كلية تنمية القدرات الجامعية - خانيونس'],
            ['institution_id' => 9, 'name' => 'كلية فلسطين التقنية - دير البلح'],
            ['institution_id' => 12, 'name' => 'كلية مجتمع الأقصى للدراسات المتوسطة'],
        ];

        foreach ($colleges as $col) {
            College::create($col);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
