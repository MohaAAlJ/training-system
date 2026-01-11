<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('hide_full_sections')->default(true);
            $table->boolean('is_public_form_enabled')->default(true);
            $table->boolean('enable_training_type_practice')->default(true);
            $table->boolean('enable_training_type_university')->default(true);
            $table->boolean('can_university_reapply')->default(false);
            $table->boolean('can_practice_reapply')->default(false);
            $table->boolean('hoa_can_edit_section')->default(false);
            $table->boolean('hoa_can_enable_section')->default(false);
            $table->boolean('dept_head_can_edit_section')->default(false);
            $table->boolean('dept_head_can_enable_section')->default(false);
            $table->timestamps();
        });

        // Create initial record
        DB::table('training_settings')->insert([
            'hide_full_sections' => true,
            'is_public_form_enabled' => true,
            'enable_training_type_practice' => true,
            'enable_training_type_university' => true,
            'can_university_reapply' => false,
            'can_practice_reapply' => false,
            'hoa_can_edit_section' => false,
            'hoa_can_enable_section' => false,
            'dept_head_can_edit_section' => false,
            'dept_head_can_enable_section' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('training_settings');
    }
};
