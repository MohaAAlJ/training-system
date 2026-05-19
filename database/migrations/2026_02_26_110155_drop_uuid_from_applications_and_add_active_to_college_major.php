<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop uuid from applications table
        if (Schema::hasColumn('applications', 'uuid')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }

        // Add active flag to college_major pivot
        Schema::table('college_major', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('major_id');
        });
    }

    public function down(): void
    {
        Schema::table('college_major', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        // Restore uuid on applications if rolled back
        if (!Schema::hasColumn('applications', 'uuid')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });
        }
    }
};
