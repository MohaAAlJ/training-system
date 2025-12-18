<?php

use App\Models\Trainees;
use App\Models\Sections;
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
            $table->unsignedBigInteger('trainee_id')->nullable();
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->index('trainee_id');

            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->index('section_id');

            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->index('department_id');
            // Application-specific fields
            $table->string('street')->nullable();
            $table->integer('duration')->nullable();
            $table->string('training_type')->nullable();

            // Application lifecycle
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'waiting', 'active', 'completed', 'rejected', 'paused'])->default('pending');
            $table->string('application_letter')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->string('tags')->nullable();

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
