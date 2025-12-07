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
        // Drop the old institution_major_id foreign key constraint
        Schema::table('trainees', function (Blueprint $table) {
            if (Schema::hasColumn('trainees', 'institution_major_id')) {
                $table->dropForeign(['institution_major_id']);
                $table->dropColumn('institution_major_id');
            }
        });

        // Create the trainee_major pivot table for many-to-many relationship
        Schema::create('trainee_major', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trainee_id')
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->foreignId('major_id')
                ->constrained('majors')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['trainee_id', 'major_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_major');

        Schema::table('trainees', function (Blueprint $table) {
            $table->foreignId('institution_major_id')
                ->nullable()
                ->constrained('institution_majors')
                ->nullOnDelete();
        });
    }
};
