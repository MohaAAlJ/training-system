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

            // Basic Info
            $table->string('national_id')->unique(); // Unique national ID
            $table->string('full_name');
            $table->string('phone_number');
            $table->date('dob'); // Date of Birth
            $table->string('location')->nullable(); // Optional address/location

            // Foreign Keys
            // If a university or major is deleted, set to null for history preservation
            $table->foreignId('university_id')
                ->nullable()
                ->constrained('universities')
                ->nullOnDelete();
            $table->index('university_id');

            $table->foreignId('major_id')
                ->nullable()
                ->constrained('majors')
                ->nullOnDelete();
            $table->index('major_id');

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
