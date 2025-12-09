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

            // Personal & contact info
            $table->string('full_name');
            $table->string('dob', 10); // dd/mm/yyyy
            $table->string('national_id', 10);
            $table->string('phone_number', 12);
            $table->string('address');
            $table->string('street');

            // Educational & training details
            $table->foreignId('institution_id')->constrained('institutions');
            $table->foreignId('major_id')->constrained('majors');
            $table->integer('training_hours');
            $table->foreignId('administrative_id')->constrained('administratives');
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('training_type', 100)->nullable();

            // Optional association to a trainee record (nullable for public form submissions)
            $table->foreignId('trainee_id')->nullable()->constrained('trainees')->nullOnDelete();

            // Application lifecycle
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'active', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->string('letter_image_path')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->string('tags')->nullable();

            // Slug for public lookup/slugged endpoint
            $table->string('slug')->unique();

            $table->timestamps();
            $table->softDeletes();
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
