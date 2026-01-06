<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Application;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Populate existing records safely
        Application::chunk(100, function ($applications) {
            foreach ($applications as $application) {
                $application->update(['uuid' => (string) Str::uuid()]);
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
