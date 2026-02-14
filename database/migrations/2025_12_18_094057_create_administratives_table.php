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
            $table->string('name');
            $table->boolean('is_medical')->default(App\Enums\GeneralConst::INACTIVE);
            $table->foreignIdFor(User::class)->nullable()->constrained()->onDelete('restrict');
            $table->foreignIdFor(User::class, 'medical_head_user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignIdFor(Governorate::class)->nullable()->constrained()->onDelete('restrict');
            $table->boolean('active')->default(App\Enums\GeneralConst::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed data: Centers from image (Medical) + HQ units (Non-Medical)
        $administratives = [
            // Medical Centers (from image)
            ['name' => 'مستشفى الأمل', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'مدينة الأمل', 'governorate' => 'خانيونس', 'is_medical' => false],
            ['name' => 'مستشفى المواصي الميداني', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'مدينة النور', 'governorate' => 'غزة', 'is_medical' => false],
            ['name' => 'مستشفى القدس', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'مستشفى السرايا الميداني', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية المينا', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'النقطة الطبية مواصي القرارة', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'عيادة المواصي', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'عيادة م.الأمل', 'governorate' => 'خانيونس', 'is_medical' => true],
            ['name' => 'النقطة الطبية الزوايدة', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'النقطة الطبية النصيرات', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'النقطة الطبية السوارحة', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'عيادة مركز فتحي عرفات الطبي', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'النقطة الطبية المغازي', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'النقطة الطبية البريج', 'governorate' => 'محافظات الوسطى', 'is_medical' => true],
            ['name' => 'النقطة الطبية الصحابة', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية الصبرة', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية السرايا', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية القدس', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية الشمال', 'governorate' => 'غزة', 'is_medical' => true],
            ['name' => 'النقطة الطبية الزيتون', 'governorate' => 'غزة', 'is_medical' => true],
        ];

        foreach ($administratives as $adm) {
            $governorate = Governorate::where('name', $adm['governorate'])->first();

            Administrative::create([
                'name' => $adm['name'],
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
