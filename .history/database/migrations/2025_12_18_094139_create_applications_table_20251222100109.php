<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Trainees;
use App\Models\Administratives;
use App\Models\Departments;
use App\Models\Sections;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trainees::class, 'trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignIdFor(Administratives::class, 'administrative_id')->constrained('administratives')->cascadeOnDelete();
            $table->foreignIdFor(Departments::class, 'department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Sections::class, 'section_id')->constrained('sections')->cascadeOnDelete();

            $table->string('tags')->nullable();

            $table->tinyInteger('training_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration')->nullable();
            $table->tinyInteger('status')->default(1);

            $table->text('application_letter')->nullable();
            $table->timestamp('accepted_at')->nullable();
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
