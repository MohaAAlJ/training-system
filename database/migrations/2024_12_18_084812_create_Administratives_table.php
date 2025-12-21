<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Administratives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('medical_head_user_id')->nullable();
            $table->boolean('is_medical')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('medical_head_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // If the legacy `departments` table still has `is_medical`, drop it here
        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'is_medical')) {
            Schema::table('departments', function (Blueprint $table) {
                // drop column if possible
                $table->dropColumn('is_medical');
            });
        }

        // Make head_of_department nullable on departments (add if missing)
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (! Schema::hasColumn('departments', 'head_of_department')) {
                    $table->unsignedBigInteger('head_of_department')->nullable()->after('user_id');
                    $table->foreign('head_of_department')->references('id')->on('users')->nullOnDelete();
                } else {
                    // attempt to make existing column nullable via change() when available
                    try {
                        $table->unsignedBigInteger('head_of_department')->nullable()->change();
                    } catch (\Throwable $e) {
                        // change() requires doctrine/dbal; if not available skip silently
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // revert department schema changes first
        if (Schema::hasTable('departments') && ! Schema::hasColumn('departments', 'is_medical')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->boolean('is_medical')->default(false)->after('user_id');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'head_of_department')) {
            try {
                Schema::table('departments', function (Blueprint $table) {
                    $table->unsignedBigInteger('head_of_department')->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                // skip if change() not available
            }
        }

        Schema::dropIfExists('Administratives');
    }
};
