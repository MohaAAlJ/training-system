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
        Schema::create('telegram_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->boolean('is_active')->default(false); // Security Gate
            $table->boolean('wants_activities')->default(true); // Application Status & New Apps
            $table->boolean('wants_errors')->default(false); // System Error Logs (Default OFF)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_subscribers');
    }
};
