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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // مدير المنشأة
            $table->boolean('is_medical')->default(false);  //
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // المدير العام للمنشأة
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
