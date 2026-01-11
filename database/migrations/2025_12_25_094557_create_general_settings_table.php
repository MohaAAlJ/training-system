<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration has been replaced by 2025_01_10_000000_create_settings_table.php
     */
    public function up(): void
    {
        // Migrated to Spatie settings - no action needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Settings now stored in Spatie settings table
    }
};
