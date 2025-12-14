<?php

use App\Models\Institution;
use App\Models\User;
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
        // database/migrations/xxxx_create_colleges_table.php
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->json('name');

            $table->foreignIdFor(Institution::class)->constrained()->cascadeOnDelete();

            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
