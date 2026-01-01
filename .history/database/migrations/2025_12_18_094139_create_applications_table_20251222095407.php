<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Trainees;
use App\Models\Administrative;
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
            $table->foreignIdFor(Trainees::class)->constrained('trainees')->cascadeOnDelete();
            $table->foreignIdFor(Administrative::class)->constrained('administratives')->cascadeOnDelete();
            $table->foreignIdFor(Departments::class)->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Sections::class)->constrained('sections')->cascadeOnDelete();

            $table->string('training_type')->nullable();
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
