<?php

use App\Models\User;
use App\Models\Administrative;
use App\Models\Departments;
use App\Models\Governorate;
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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->string('address')->nullable();

            // Section Info
            $table->string('name_location'); // Section name and location
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('total_capacity');

            // Foreign Keys
            $table->foreignIdFor(User::class, 'user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->index('user_id');

            $table->foreignIdFor(Administrative::class, 'Administrative_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->index('Administrative_id');

            $table->foreignIdFor(Governorate::class, 'governorate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->index('governorate_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
