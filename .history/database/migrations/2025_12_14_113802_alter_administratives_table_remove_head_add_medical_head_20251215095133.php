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
        Schema::table('administratives', function (Blueprint $table) {
            // Drop the redundant head_of_administrative column
            if (Schema::hasColumn('administratives', 'head_of_administrative')) {
                $table->dropColumn('head_of_administrative');
            }
            
            // Add medical head foreign key
            if (!Schema::hasColumn('administratives', 'medical_head_user_id')) {
                $table->foreignIdFor(User::class, 'medical_head_user_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->index('medical_head_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administratives', function (Blueprint $table) {
            // Drop the medical_head_user_id foreign key
            if (Schema::hasColumn('administratives', 'medical_head_user_id')) {
                $table->dropForeignKeyIfExists(['medical_head_user_id']);
                $table->dropColumn('medical_head_user_id');
            }
            
            // Restore head_of_administrative column
            if (!Schema::hasColumn('administratives', 'head_of_administrative')) {
                $table->string('head_of_administrative')->after('is_medical');
            }
        });
    }
};
