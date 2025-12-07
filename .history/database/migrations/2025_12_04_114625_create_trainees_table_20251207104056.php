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
            $table->date('dob'); 
            $table->string('location')->nullable(); 

            $table->foreignId('institution_id')
                ->nullable()
                ->constrained('institutions')
                ->nullOnDelete();

            $table->foreignId('institution_major_id')
                ->nullable()
                ->constrained('institution_majors')
                ->nullOnDelete();

            $table->index('institution_id');
            $table->index('institution_major_id');

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
