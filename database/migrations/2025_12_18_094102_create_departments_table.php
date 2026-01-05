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
            $table->string('title'); // مثال: الدائرة العامة للصيدلة
            $table->boolean('is_medical')->default(false);
            $table->boolean('status')->default(true); // نشط أو غير نشط
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete(); // المدير العام للتخصص
            $table->timestamps();
            $table->softDeletes();
        });
         // Seed departments
        $departments = [
            // Medical Departments
            ['title' => 'طب عام', 'is_medical' => true],
            ['title' => 'صيدلة', 'is_medical' => true],
            ['title' => 'مختبرات', 'is_medical' => true],
            ['title' => 'أشعة', 'is_medical' => true],
            ['title' => 'تمريض', 'is_medical' => true],
            ['title' => 'علاج طبيعي', 'is_medical' => true],
            ['title' => 'علاج وظيفي', 'is_medical' => true],
            ['title' => 'صحة نفسية', 'is_medical' => true],
            ['title' => 'أسنان', 'is_medical' => true],

            // Administrative/Support Departments
            ['title' => 'إدارة', 'is_medical' => false],
            ['title' => 'مالية', 'is_medical' => false],
            ['title' => 'تكنولوجيا المعلومات', 'is_medical' => false],
            ['title' => 'العلاقات العامة و الإعلام', 'is_medical' => false],
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
