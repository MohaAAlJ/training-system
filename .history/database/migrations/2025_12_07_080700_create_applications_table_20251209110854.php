<?php

use App\Models\Trainees;
use App\Models\Departments;
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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignIdFor(Trainees::class, 'trainee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index('trainee_id');

            $table->foreignIdFor(Departments::class, 'department_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index('department_id');

            // Application Details
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'active', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->string('letter_image_path')->nullable(); // Path to letter/document image
            $table->dateTime('accepted_at')->nullable(); // When application was accepted
            $table->string('tags')->nullable(); // Additional tags/metadata

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
