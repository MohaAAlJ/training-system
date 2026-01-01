<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
    $table->string('national_id')->unique();
    $table->string('full_name');
    $table->string('phone_number');
    $table->date('dob');
    $table->string('address')->nullable();
    
    // الربط الأكاديمي
    $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('college_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('major_id')->nullable()->constrained()->nullOnDelete();
    
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
