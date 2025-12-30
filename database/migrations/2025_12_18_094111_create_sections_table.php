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
            ['administrative_id' => 1, 'department_id' => 2, 'section_name' => 'طوارئ', 'capacity' => 10],
            ['administrative_id' => 1, 'department_id' => 3, 'section_name' => 'صيدلة', 'capacity' => 6],
            ['administrative_id' => 1, 'department_id' => 4, 'section_name' => 'مختبرات', 'capacity' => 5],
            ['administrative_id' => 1, 'department_id' => 5, 'section_name' => 'أشعة', 'capacity' => 6],
            ['administrative_id' => 1, 'department_id' => 6, 'section_name' => 'تمريض', 'capacity' => 9],
            ['administrative_id' => 1, 'department_id' => 7, 'section_name' => 'علاج طبيعي', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 8, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 9, 'section_name' => 'صحة نفسية', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 10, 'section_name' => 'أسنان', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 11, 'section_name' => 'رعاية', 'capacity' => 7],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 1, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            ['administrative_id' => 1, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 5],
            // Medical sub-sections under طب عام
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'أنف وأذن وحنجرة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'أورام', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'جراحة', 'capacity' => 8],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'أطفال', 'capacity' => 7],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'نساء وولادة', 'capacity' => 9],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'عظام', 'capacity' => 6],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'قلب وأوعية دموية', 'capacity' => 6],
            ['administrative_id' => 1, 'department_id' => 1, 'section_name' => 'عناية مركزة', 'capacity' => 7],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 2],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 2],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 3],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 4],
            ['administrative_id' => 1, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 2],

            // ============================================
            // مدينة الأمل (Admin ID: 2) - CITY - خانيونس
            // ONLY ADMINISTRATIVE SECTIONS
            // ============================================
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 6],
            ['administrative_id' => 2, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 6],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 4],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 3],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 5],
            ['administrative_id' => 2, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 4],

            // ============================================
            // مستشفى المواصي (Admin ID: 3) - HOSPITAL - خانيونس
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 7],
            ['administrative_id' => 3, 'department_id' => 2, 'section_name' => 'طوارئ', 'capacity' => 9],
            ['administrative_id' => 3, 'department_id' => 3, 'section_name' => 'صيدلة', 'capacity' => 5],
            ['administrative_id' => 3, 'department_id' => 4, 'section_name' => 'مختبرات', 'capacity' => 6],
            ['administrative_id' => 3, 'department_id' => 5, 'section_name' => 'أشعة', 'capacity' => 5],
            ['administrative_id' => 3, 'department_id' => 6, 'section_name' => 'تمريض', 'capacity' => 10],
            ['administrative_id' => 3, 'department_id' => 7, 'section_name' => 'علاج طبيعي', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 8, 'section_name' => 'علاج وظيفي', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 9, 'section_name' => 'صحة نفسية', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 10, 'section_name' => 'أسنان', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 11, 'section_name' => 'رعاية', 'capacity' => 6],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 4],
            // Medical sub-sections under طب عام
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'أنف وأذن وحنجرة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'أورام', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'جراحة', 'capacity' => 7],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'أطفال', 'capacity' => 6],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'نساء وولادة', 'capacity' => 8],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'عظام', 'capacity' => 5],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'قلب وأوعية دموية', 'capacity' => 6],
            ['administrative_id' => 3, 'department_id' => 1, 'section_name' => 'عناية مركزة', 'capacity' => 6],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 2],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 3],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 4],
            ['administrative_id' => 3, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 2],

            // ============================================
            // مدينة القدس (Admin ID: 4) - CITY - غزة
            // ONLY ADMINISTRATIVE SECTIONS
            // ============================================
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 6],
            ['administrative_id' => 4, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 6],
            ['administrative_id' => 4, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 6],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 4],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 5],
            ['administrative_id' => 4, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 4],

            // ============================================
            // مستشفى القدس (Admin ID: 5) - HOSPITAL - غزة
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 9],
            ['administrative_id' => 5, 'department_id' => 2, 'section_name' => 'طوارئ', 'capacity' => 10],
            ['administrative_id' => 5, 'department_id' => 3, 'section_name' => 'صيدلة', 'capacity' => 6],
            ['administrative_id' => 5, 'department_id' => 4, 'section_name' => 'مختبرات', 'capacity' => 6],
            ['administrative_id' => 5, 'department_id' => 5, 'section_name' => 'أشعة', 'capacity' => 7],
            ['administrative_id' => 5, 'department_id' => 6, 'section_name' => 'تمريض', 'capacity' => 10],
            ['administrative_id' => 5, 'department_id' => 7, 'section_name' => 'علاج طبيعي', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 8, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 9, 'section_name' => 'صحة نفسية', 'capacity' => 5],
            ['administrative_id' => 5, 'department_id' => 10, 'section_name' => 'أسنان', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 11, 'section_name' => 'رعاية', 'capacity' => 8],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 5],
            // Medical sub-sections under طب عام
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'أنف وأذن وحنجرة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'أورام', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'جراحة', 'capacity' => 9],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'أطفال', 'capacity' => 8],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'نساء وولادة', 'capacity' => 10],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'عظام', 'capacity' => 7],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'قلب وأوعية دموية', 'capacity' => 7],
            ['administrative_id' => 5, 'department_id' => 1, 'section_name' => 'عناية مركزة', 'capacity' => 7],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 2],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 3],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 4],
            ['administrative_id' => 5, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 3],

            // ============================================
            // مستشفى السرايا (Admin ID: 6) - HOSPITAL - غزة
            // ALL SECTIONS (Medical + Administrative)
            // ============================================
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'طب عام', 'capacity' => 7],
            ['administrative_id' => 6, 'department_id' => 2, 'section_name' => 'طوارئ', 'capacity' => 8],
            ['administrative_id' => 6, 'department_id' => 3, 'section_name' => 'صيدلة', 'capacity' => 5],
            ['administrative_id' => 6, 'department_id' => 4, 'section_name' => 'مختبرات', 'capacity' => 5],
            ['administrative_id' => 6, 'department_id' => 5, 'section_name' => 'أشعة', 'capacity' => 6],
            ['administrative_id' => 6, 'department_id' => 6, 'section_name' => 'تمريض', 'capacity' => 9],
            ['administrative_id' => 6, 'department_id' => 7, 'section_name' => 'علاج طبيعي', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 8, 'section_name' => 'علاج وظيفي', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 9, 'section_name' => 'صحة نفسية', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 10, 'section_name' => 'أسنان', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 11, 'section_name' => 'رعاية', 'capacity' => 6],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'إدارة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 13, 'section_name' => 'مالية', 'capacity' => 2],
            ['administrative_id' => 6, 'department_id' => 14, 'section_name' => 'موارد بشرية', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 15, 'section_name' => 'تكنولوجيا المعلومات', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 16, 'section_name' => 'الإعلام والعلاقات العامة', 'capacity' => 2],
            ['administrative_id' => 6, 'department_id' => 17, 'section_name' => 'تدريب وتأهيل', 'capacity' => 4],
            // Medical sub-sections under طب عام
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'أنف وأذن وحنجرة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'أورام', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'جراحة', 'capacity' => 7],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'أطفال', 'capacity' => 7],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'نساء وولادة', 'capacity' => 8],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'عظام', 'capacity' => 5],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'قلب وأوعية دموية', 'capacity' => 6],
            ['administrative_id' => 6, 'department_id' => 1, 'section_name' => 'عناية مركزة', 'capacity' => 6],
            // Administrative sub-sections under إدارة
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'مشتريات', 'capacity' => 2],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'خدمات عامة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'صيانة', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'مخازن', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'شؤون قانونية', 'capacity' => 2],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'الأمن والسلامة', 'capacity' => 3],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'نظافة وإشراف بيئي', 'capacity' => 4],
            ['administrative_id' => 6, 'department_id' => 12, 'section_name' => 'تخطيط وتطوير', 'capacity' => 2],
        ];

        foreach ($sections as $sec) {
            $admin = Administrative::find($sec['administrative_id']);

            Section::create([
                'name_location'     => $sec['section_name'] . ' - ' . $admin->title,
                'administrative_id' => $sec['administrative_id'],
                'department_id'     => $sec['department_id'],
                'capacity'          => $sec['capacity'],
                'status'            => true
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
