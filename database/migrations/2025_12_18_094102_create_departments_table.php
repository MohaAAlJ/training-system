<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Department;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // مثال: الدائرة العامة للصيدلة
            $table->boolean('is_medical')->default(App\Enums\GeneralConst::INACTIVE);
            $table->boolean('active')->default(App\Enums\GeneralConst::ACTIVE); // نشط أو غير نشط
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete(); // المدير العام للتخصص
            $table->timestamps();
            $table->softDeletes();
        });
         // Seed departments
        $departments = [
            // Medical Departments
            ['name' => 'طب عام', 'is_medical' => true],
            ['name' => 'صيدلة', 'is_medical' => true],
            ['name' => 'مختبرات', 'is_medical' => true],
            ['name' => 'أشعة', 'is_medical' => true],
            ['name' => 'تمريض', 'is_medical' => true],
            ['name' => 'علاج طبيعي', 'is_medical' => true],
            ['name' => 'علاج وظيفي', 'is_medical' => true],
            ['name' => 'صحة نفسية', 'is_medical' => true],
            ['name' => 'أسنان', 'is_medical' => true],
            // Administrative/Support Departments
            ['name' => 'إدارة', 'is_medical' => false],
            ['name' => 'مالية', 'is_medical' => false],
            ['name' => 'تكنولوجيا المعلومات', 'is_medical' => false],
            ['name' => 'العلاقات العامة و الإعلام', 'is_medical' => false],
        ];

        foreach ($departments as $deptData) {
            Department::create($deptData);
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
