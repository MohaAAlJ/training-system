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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('position_applied');
            $table->text('cover_letter');
            $table->string('resume_path');
            $table->json('full_name')->nullable();
            $table->json('institution_name')->nullable();
            $table->json('major')->nullable();
            $table->json('address')->nullable();
            $table->json('reason_for_rejection')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('major_level')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
