<?php

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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->string('address')->nullable();

            // Department Info
            $table->string('name_location'); // Department name and location
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('total_capacity');

            // Foreign Keys
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->index('user_id');

            $table->foreignIdFor('App\Models\Administratives', 'administrative_id')
                ->nullable()
                ->constrained('administratives')
                ->nullOnDelete();
            $table->index('administrative_id');

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
