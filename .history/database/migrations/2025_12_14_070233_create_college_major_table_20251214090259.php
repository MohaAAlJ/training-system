<?php

use App\Models\College;
use App\Models\Major;
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
        Schema::create('college_major', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->cascadeOnDelete();
            $table->foreignId('major_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // منع التكرار (نفس التخصص لا يضاف لنفس الكلية مرتين)
            $table->unique(['college_id', 'major_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_major');
    }
};
