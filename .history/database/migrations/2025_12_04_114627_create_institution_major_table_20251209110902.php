<?php

use App\Models\Institution;
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
        Schema::create('institution_major', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignIdFor(Institution::class, 'institution_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index('institution_id');

            $table->foreignIdFor(Major::class, 'major_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index('major_id');

            // Timestamps
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['institution_id', 'major_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_major');
    }
};
