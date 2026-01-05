<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Department;
use App\Models\Administrative;
use App\Models\Governorate;
use App\Models\Section;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name_location');
            $table->foreignIdFor(Administrative::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Department::class, 'department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Governorate::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->integer('capacity')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $sections = [
            // ============================================
            // مستشفى الأمل (Admin ID: 1) - HOSPITAL - خانيونس
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 8],
            ['administrative_id' => 1, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 5],
            ['administrative_id' => 1, 'department_id' => 4, 'section_name' => 'أشعة', 'capacity' => 6],
            ['administrative_id' => 1, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 22],
            ['administrative_id' => 1, 'department_id' => 6, 'section_name' => 'علاج طبيعي', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 7, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 8, 'section_name' => 'صحة نفسية', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 9, 'section_name' => 'أسنان', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 2],

            // ============================================
            // مدينة الأمل (Admin ID: 2) - CITY - خانيونس
            // ONLY ADMINISTRATIVE SECTIONS
            // ============================================
            ['administrative_id' => 2, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 6],
            ['administrative_id' => 2, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 4],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 2, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 3],

            // ============================================
            // مستشفى المواصي (Admin ID: 3) - HOSPITAL - خانيونس
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 7],
            ['administrative_id' => 3, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 6],
            ['administrative_id' => 3, 'department_id' => 4, 'section_name' => 'أشعة', 'capacity' => 5],
            ['administrative_id' => 3, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 10],
            ['administrative_id' => 3, 'department_id' => 6, 'section_name' => 'علاج طبيعي', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 7, 'section_name' => 'علاج وظيفي', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 8, 'section_name' => 'صحة نفسية', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 9, 'section_name' => 'أسنان', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 2],

            // ============================================
            // مدينة القدس (Admin ID: 4) - CITY - غزة
            // ONLY ADMINISTRATIVE SECTIONS
            // ============================================
            ['administrative_id' => 4, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 6],
            ['administrative_id' => 4, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 6],
            ['administrative_id' => 4, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 5],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 4, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 4],

            // ============================================
            // مستشفى القدس (Admin ID: 5) - HOSPITAL - غزة
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 9],
            ['administrative_id' => 5, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 6],
            ['administrative_id' => 5, 'department_id' => 4, 'section_name' => 'أشعة', 'capacity' => 7],
            ['administrative_id' => 5, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 10],
            ['administrative_id' => 5, 'department_id' => 6, 'section_name' => 'علاج طبيعي', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 7, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 8, 'section_name' => 'صحة نفسية', 'capacity' => 5],
            ['administrative_id' => 5, 'department_id' => 9, 'section_name' => 'أسنان', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 3],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 2],

            // ============================================
            // مستشفى السرايا (Admin ID: 6) - HOSPITAL - غزة
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 7],
            ['administrative_id' => 6, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 5],
            ['administrative_id' => 6, 'department_id' => 4, 'section_name' => 'أشعة', 'capacity' => 6],
            ['administrative_id' => 6, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 9],
            ['administrative_id' => 6, 'department_id' => 6, 'section_name' => 'علاج طبيعي', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 7, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 8, 'section_name' => 'صحة نفسية', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 9, 'section_name' => 'أسنان', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 2],

            // ============================================
            // النقاط الطبية والعيادات (Admin IDs 7-22)
            // MEDICAL CENTERS - SIMPLIFIED SECTIONS
            // ============================================
            // Admin ID 7: النقطة الطبية المينا
            ['administrative_id' => 7, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 7, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 7, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 7, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 7, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 7, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 8: النقطة الطبية مواصي القرارة
            ['administrative_id' => 8, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 8, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 8, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 8, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 8, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 8, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 9: عيادة المواصي
            ['administrative_id' => 9, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 9, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 3],
            ['administrative_id' => 9, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 9, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 9, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 9, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 10: عيادة م.الأمل
            ['administrative_id' => 10, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 10, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 5],
            ['administrative_id' => 10, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 10, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 10, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 10, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 11: النقطة الطبية الزوايدة
            ['administrative_id' => 11, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 11, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 11, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 11, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 11, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 11, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 12: النقطة الطبية النصيرات
            ['administrative_id' => 12, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 12, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 12, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 12, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 12, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 12, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 13: النقطة الطبية السوارحة
            ['administrative_id' => 13, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 13, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 13, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 13, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 13, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 13, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 14: عيادة مركز فتحي عرفات الطبي
            ['administrative_id' => 14, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 6],
            ['administrative_id' => 14, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 5],
            ['administrative_id' => 14, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 14, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 14, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 14, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 15: النقطة الطبية المغازي
            ['administrative_id' => 15, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 15, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 15, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 15, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 15, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 15, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 16: النقطة الطبية البريج
            ['administrative_id' => 16, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 16, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 16, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 16, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 16, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 16, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 17: النقطة الطبية الصحابة
            ['administrative_id' => 17, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 17, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 17, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 17, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 17, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 17, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 18: النقطة الطبية الصبرة
            ['administrative_id' => 18, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 18, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 18, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 18, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 18, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 18, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 19: النقطة الطبية السرايا
            ['administrative_id' => 19, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 19, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 19, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 19, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 19, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 19, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 20: النقطة الطبية القدس
            ['administrative_id' => 20, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 20, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 20, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 20, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 20, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 20, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 21: النقطة الطبية الشمال
            ['administrative_id' => 21, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 5],
            ['administrative_id' => 21, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 21, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 3],
            ['administrative_id' => 21, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 4],
            ['administrative_id' => 21, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 21, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // Admin ID 22: النقطة الطبية الزيتون
            ['administrative_id' => 22, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 4],
            ['administrative_id' => 22, 'department_id' => 2, 'section_name' => 'صيدلة', 'capacity' => 2],
            ['administrative_id' => 22, 'department_id' => 3, 'section_name' => 'مختبرات', 'capacity' => 2],
            ['administrative_id' => 22, 'department_id' => 5, 'section_name' => 'تمريض', 'capacity' => 3],
            ['administrative_id' => 22, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 1],
            ['administrative_id' => 22, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 2],

            // ============================================
            // مدينة النور (Admin ID 23) - CITY - غزة
            // ONLY ADMINISTRATIVE SECTIONS
            // ============================================
            ['administrative_id' => 23, 'department_id' => 10, 'section_name' => 'إدارة', 'capacity' => 5],
            ['administrative_id' => 23, 'department_id' => 11, 'section_name' => 'مالية', 'capacity' => 3],
            ['administrative_id' => 23, 'department_id' => 12, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 5],
            ['administrative_id' => 23, 'department_id' => 13, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 3],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 23, 'department_id' => 10, 'section_name' => 'خدمات عامة', 'capacity' => 4],
            ['administrative_id' => 23, 'department_id' => 10, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 23, 'department_id' => 10, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 23, 'department_id' => 10, 'section_name' => 'شؤون قانونية', 'capacity' => 2],
        ];



        foreach ($sections as $sec) {
            $admin = Administrative::find($sec['administrative_id']);
            if (! $admin) {
                continue;
            }

            Section::create([
                'name_location'     => $sec['section_name'] . ' - ' . $admin->title,
                'administrative_id' => $sec['administrative_id'],
                'department_id'     => $sec['department_id'],
                'capacity'          => $sec['capacity'],
                'status'            => true,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
