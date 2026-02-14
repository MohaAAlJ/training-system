<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\College;
use App\Models\Major;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('national_id')->nullable()->unique();
            $table->string('full_name');
            $table->string('phone_number')->nullable();
            $table->date('dob')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->foreignIdFor(Governorate::class)->nullable()->constrained()->onDelete('restrict');
            $table->string('street')->nullable();
            // الربط الأكاديمي
            $table->foreignIdFor(Institution::class)->nullable()->constrained()->onDelete('restrict');
            $table->foreignIdFor(College::class)->nullable()->constrained()->onDelete('restrict');
            $table->foreignIdFor(Major::class)->nullable()->constrained()->onDelete('restrict');
            $table->string('university_number')->nullable();
            $table->integer('training_hours')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
