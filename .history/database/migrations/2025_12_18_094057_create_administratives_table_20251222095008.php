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
        Schema::create('administratives', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // مثال: إدارة مستشفى الأمل
            $table->boolean('is_medical')->default(false);
            $table->foreignIdFor(User::)->constrained('users')->cascadeOnDelete(); // مدير المنشأة
            $table->foreignId('medical_head_user_id')->nullable()->constrained('users')->nullOnDelete(); // رئيس الإدارة الطبية
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administratives');
    }
};
