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
        Schema::create('department_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'section_id']);
        });

        // DATA MIGRATION: Populate pivot table from existing sections
        \Illuminate\Support\Facades\DB::statement("
            INSERT INTO department_section (department_id, section_id, created_at, updated_at)
            SELECT department_id, id, NOW(), NOW()
            FROM sections
            WHERE department_id IS NOT NULL
        ");

        // Drop the now-redundant department_id column from sections
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore department_id column to sections before removing the pivot table
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::dropIfExists('department_section');
    }
};
