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

        // Pharmacy capacities from the image (Mapped to new administrative titles)
        $pharmacyCapacities = [
            'مستشفى السرايا الميداني' => 3,
            'مستشفى القدس' => 3,
            'مستشفى المواصي الميداني' => 2,
            'مستشفى الأمل' => 3,
            'النقطة الطبية المينا' => 2,
            'النقطة الطبية مواصي القرارة' => 2,
            'عيادة المواصي' => 3,
            'عيادة م.الأمل' => 5,
            'النقطة الطبية الزوايدة' => 2,
            'النقطة الطبية النصيرات' => 2,
            'النقطة الطبية السوارحة' => 2,
            'عيادة مركز فتحي عرفات الطبي' => 5,
            'النقطة الطبية المغازي' => 2,
            'النقطة الطبية البريج' => 2,
            'النقطة الطبية الصحابة' => 2,
            'النقطة الطبية الصبرة' => 2,
            'النقطة الطبية السرايا' => 2,
            'النقطة الطبية القدس' => 2,
            'النقطة الطبية الشمال' => 2,
            'النقطة الطبية الزيتون' => 2,
        ];

        $administratives = Administrative::all();
        $deptMap = Department::pluck('id', 'title')->toArray();

        foreach ($administratives as $admin) {
            $deptTitles = [];

            // 1. Determine which departments to add based on type
            if (str_contains($admin->title, 'مستشفى')) {
                // Hospital: Comprehensive list
                $deptTitles = [
                    'طب عام', 'طوارئ', 'صيدلة', 'مختبرات', 'أشعة', 'تمريض',
                    'علاج طبيعي', 'علاج وظيفي', 'صحة نفسية', 'أسنان', 'رعاية',
                    'إدارة', 'مالية', 'موارد بشرية', 'تكنولوجيا المعلومات',
                    'الإعلام والعلاقات العامة', 'تدريب وتأهيل'
                ];
            } elseif (str_contains($admin->title, 'نقطة')) {
                // Medical Point: Emergency (Medicine), Nursing, Pharmacy, Finance
                $deptTitles = ['طب عام', 'صيدلة', 'تمريض', 'مالية'];
            } elseif (str_contains($admin->title, 'عيادة')) {
                // Clinic: Simple medical fields
                $deptTitles = ['طب عام', 'صيدلة', 'تمريض', 'أسنان', 'رعاية', 'مالية'];
            } elseif (str_contains($admin->title, 'مدينة') || !$admin->is_medical) {
                // Administrative building (City): HQ Departments
                $deptTitles = [
                    'إدارة', 'مالية', 'موارد بشرية', 'تكنولوجيا المعلومات',
                    'الإعلام والعلاقات العامة', 'تدريب وتأهيل'
                ];
            }

            // Ensure 'مالية' is in every administrative as requested
            if (!in_array('مالية', $deptTitles)) {
                $deptTitles[] = 'مالية';
            }

            // 2. Create the sections
            foreach ($deptTitles as $title) {
                if (!isset($deptMap[$title])) continue;

                $deptId = $deptMap[$title];

                // Set capacity: Accurate for pharmacy, default for others
                $capacity = 5; // Default for non-pharmacy
                if ($title === 'صيدلة' && isset($pharmacyCapacities[$admin->title])) {
                    $capacity = $pharmacyCapacities[$admin->title];
                }

                Section::create([
                    'name_location'     => $title . ' - ' . $admin->title,
                    'administrative_id' => $admin->id,
                    'department_id'     => $deptId,
                    'governorate_id'    => $admin->governorate_id,
                    'capacity'          => $capacity,
                    'status'            => true
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
