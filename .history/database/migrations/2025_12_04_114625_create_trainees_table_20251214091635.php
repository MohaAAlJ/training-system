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
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->foreignId('college_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone_number');
            $table->date('dob');
            $table->string('address')->nullable();

            $table->foreignIdFor(Institution::class, 'institution_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(Major::class, 'major_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->index('institution_id');
            $table->index('major_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
