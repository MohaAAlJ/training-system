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
        // database/migrations/xxxx_create_colleges_table.php
Schema::create('colleges', function (Blueprint $table) {
    $table->id();
    // الاسم (يمكن استخدام JSON كما فعلت سابقاً لدعم العربية والإنجليزية)
    $table->json('name'); 
    
    // الربط مع الجامعة (الأب)
    $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
    
    // الربط مع المستخدم (مشرف الكلية)
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
