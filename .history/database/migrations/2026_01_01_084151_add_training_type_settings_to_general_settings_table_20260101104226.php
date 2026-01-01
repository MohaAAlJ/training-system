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
        Schema::table('general_settings', function (Blueprint $col) {
            $col->boolean('enable_training_type_practice')->default(true)->after('is_public_form_enabled');
            $col->boolean('enable_training_type_university')->default(true)->after('enable_training_type_practice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $col) {
            $col->dropColumn(['enable_training_type_practice', 'enable_training_type_university']);
        });
    }
};
