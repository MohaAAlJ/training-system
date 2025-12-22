<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // مثال: الدائرة العامة للصيدلة
            $table->boolean('is_medical')->default(false);
            $table->boolean('status')->default(true); // نشط أو غير نشط
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete(); // المدير العام للتخصص
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
