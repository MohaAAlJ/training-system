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
            $table->string('national_id')->unique(); // Marked as UK (Unique Key) in ERD
            $table->string('full_name');
            $table->string('phone_number');
            $table->date('dob'); // Date of Birth
            $table->string('location')->nullable(); // Address/Location

            // Relationships (Foreign Keys)
            // Assuming 'universities' and 'majors' tables exist.
            // using nullable() + nullOnDelete() is safer for history preservation if a university/major is deleted.

            $table->foreignId('university_id')
                ->nullable()
                ->constrained('universities')
                ->nullOnDelete();

            $table->foreignId('major_id')
                ->nullable()
                ->constrained('majors')
                ->nullOnDelete();

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
