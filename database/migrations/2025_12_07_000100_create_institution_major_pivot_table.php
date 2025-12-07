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
        Schema::create('institution_major', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            $table->foreignId('major_id')
                ->constrained('majors')
                ->cascadeOnDelete();

            // Timestamps
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['institution_id', 'major_id']);
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
