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

        // Seed data
        $administratives = [
            ['title' => 'مستشفى الأمل', 'governorate' => 'خانيونس'],
            ['title' => 'مدينة الأمل', 'governorate' => 'خانيونس'],
            ['title' => 'مستشفى المواصي', 'governorate' => 'خانيونس'],
            ['title' => 'مدينة القدس', 'governorate' => 'غزة'],
            ['title' => 'مستشفى القدس', 'governorate' => 'غزة'],
            ['title' => 'مستشفى السرايا', 'governorate' => 'غزة'],
        ];

        foreach ($administratives as $administrative) {
            $governorate = Governorate::where('name', $administrative['governorate'])->first();

            Administrative::create([
                'title' => $administrative['title'],
                'governorate_id' => $governorate?->id,
                'is_medical' => str_contains($administrative['title'], 'مستشفى'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('administratives');
    }
};
