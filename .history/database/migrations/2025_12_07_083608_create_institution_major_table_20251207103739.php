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

            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            $table->foreignId('institution_major_id')
                ->constrained('institution_majors')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['institution_id', 'institution_major_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_major');
    }
};
