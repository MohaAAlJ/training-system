<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Administratives;
use App\Models\Departments;
use App\Models\Governorates;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name_location'); // مثال: صيدلية فرع خانيونس
            $table->foreignIdFor(Administratives::class, 'administrative_id')->constrained('administratives')->cascadeOnDelete();
            $table->foreignIdFor(Departments::class, 'department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Governorates::class, 'governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignIdFor(User::class, 'hos')->nullable()->constrained('users')->nullOnDelete(); // رئيس الشعبة
            $table->integer('total_capacity')->default(0);
            $table->tinyInteger('status')->default(1); // حالة الفرع
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
