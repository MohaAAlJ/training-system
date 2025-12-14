<?php

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
        Schema::create('administratives', function (Blueprint $table) {
            $table->id();

            // Administrative Info
            $table->string('title'); // Administrative title/role
            $table->string('head_of_administrative'); // Head of this administrative unit
            Schema::table('administratives', function (Blueprint $table) {
                $table->boolean('is_medical')->default(false)->after('title');
            });

            // Foreign Keys
            $table->foreignIdFor(User::class, 'user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index('user_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administratives');
    }
};
