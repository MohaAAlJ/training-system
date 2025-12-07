<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_institution_major_table.php
public function up(): void
{
    Schema::create('institution_major', function (Blueprint $table) {
        $table->id();
        
        // Foreign Key to Institutions
        $table->foreignId('institution_id')
              ->constrained('institutions')
              ->cascadeOnDelete();

        // Foreign Key to Majors
        $table->foreignId('major_id')
              ->constrained('majors')
              ->cascadeOnDelete();

        // Optional: timestamps if you want to know when the major was added to the uni
        $table->timestamps();

        // Constraint: Prevent adding the same major to the same university twice
        $table->unique(['institution_id', 'major_id']); 
    });
}
};
