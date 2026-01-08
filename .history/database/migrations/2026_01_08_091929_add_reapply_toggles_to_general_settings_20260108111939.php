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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('can_university_reapply')->default(false)->after('enable_training_type_university');
            $table->boolean('can_practice_reapply')->default(false)->after('enable_training_type_practice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['can_university_reapply', 'can_practice_reapply']);
        });
    }
};
