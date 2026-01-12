<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Trainee;
use App\Models\Administrative;
use App\Models\Department;
use App\Models\Section;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(Trainee::class, 'trainee_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Administrative::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Department::class, 'department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Section::class, 'section_id')->constrained('sections')->cascadeOnDelete();

            $table->tinyInteger('training_type')->default(1);
            $table->integer('duration')->nullable();
            $table->string('street')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('status')->default(1);

            $table->text('tags')->nullable();
            $table->text('application_letter')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for fast application lookups
            $table->index(['trainee_id', 'training_type', 'status'], 'idx_applications_trainee_type_status');
            $table->index('trainee_id', 'idx_applications_trainee_id');
            $table->index('training_type', 'idx_applications_training_type');
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
