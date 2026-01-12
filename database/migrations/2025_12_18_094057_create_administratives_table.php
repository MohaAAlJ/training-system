<?php

use App\Models\Administrative;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administratives', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('is_medical')->default(false);
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'medical_head_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Governorate::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed data: Centers from image (Medical) + HQ units (Non-Medical)
        $administratives = [
            // Medical Centers (from image)
            ['title' => 'مستشفى الأمل', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'مدينة الأمل', 'governorate' => 'خانيونس', 'is_medical' => false],
            ['title' => 'مستشفى المواصي الميداني', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'مدينة النور', 'governorate' => 'غزة', 'is_medical' => false],
            ['title' => 'مستشفى القدس', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'مستشفى السرايا الميداني', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية المينا', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'النقطة الطبية مواصي القرارة', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'عيادة المواصي', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'عيادة م.الأمل', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['title' => 'النقطة الطبية الزوايدة', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'النقطة الطبية النصيرات', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'النقطة الطبية السوارحة', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'عيادة مركز فتحي عرفات الطبي', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'النقطة الطبية المغازي', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'النقطة الطبية البريج', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['title' => 'النقطة الطبية الصحابة', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية الصبرة', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية السرايا', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية القدس', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية الشمال', 'governorate' => 'غزة', 'is_medical' => true],
            ['title' => 'النقطة الطبية الزيتون', 'governorate' => 'غزة', 'is_medical' => true],

            // Non-Medical Administratives
            // ['title' => 'مدينة القدس', 'governorate' => 'غزة', 'is_medical' => false],
        ];

        foreach ($administratives as $adm) {
            $governorate = Governorate::where('name', $adm['governorate'])->first();

            Administrative::create([
                'title' => $adm['title'],
                'governorate_id' => $governorate?->id,
                'is_medical' => $adm['is_medical'],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('administratives');
    }
};
