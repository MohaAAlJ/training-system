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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('hoa_can_edit_section')->default(false);
            $table->boolean('hoa_can_enable_section')->default(false); // Can toggle active status
            $table->boolean('dept_head_can_edit_section')->default(false);
            $table->boolean('dept_head_can_enable_section')->default(false);
            $table->timestamps();
        });

        // Seed default record
        DB::table('general_settings')->insert([
            'hoa_can_edit_section' => false,
            'hoa_can_enable_section' => false,
            'dept_head_can_edit_section' => false,
            'dept_head_can_enable_section' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
