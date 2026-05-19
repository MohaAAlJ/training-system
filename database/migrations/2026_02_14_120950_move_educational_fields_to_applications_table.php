<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Institution;
use App\Models\College;
use App\Models\Major;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'university_number')) {
                $table->string('university_number')->nullable();
            }
            if (!Schema::hasColumn('applications', 'institution_id')) {
                $table->foreignIdFor(Institution::class)->after('university_number')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('applications', 'college_id')) {
                $table->foreignIdFor(College::class)->after('institution_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('applications', 'major_id')) {
                $table->foreignIdFor(Major::class)->after('college_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('applications', 'training_hours')) {
                $table->integer('training_hours')->after('major_id')->nullable();
            }
        });

        // Migrate data
        $trainees = \Illuminate\Support\Facades\DB::table('trainees')->get();
        foreach ($trainees as $trainee) {
            \Illuminate\Support\Facades\DB::table('applications')
                ->where('trainee_id', $trainee->id)
                ->update([
                    'institution_id' => $trainee->institution_id,
                    'college_id' => $trainee->college_id,
                    'major_id' => $trainee->major_id,
                    'university_number' => $trainee->university_number,
                    'training_hours' => $trainee->training_hours,
                ]);
        }

        // Remove columns from trainees
        Schema::table('trainees', function (Blueprint $table) {
            // Drop foreign keys first if they exist
            // Note: constraint names might vary, so we try standardized names or catch errors if cleaner, 
            // but for migration strictly, we usually assume standard names like trainees_institution_id_foreign
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['college_id']);
            $table->dropForeign(['major_id']);

            $table->dropColumn(['institution_id', 'college_id', 'major_id', 'university_number', 'training_hours']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->foreignIdFor(Institution::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(College::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Major::class)->nullable()->constrained()->nullOnDelete();
            $table->string('university_number')->nullable();
            $table->integer('training_hours')->nullable();
        });

        // Restore data from latest application
        $trainees = \Illuminate\Support\Facades\DB::table('trainees')->get();
        foreach ($trainees as $trainee) {
            $latestApp = \Illuminate\Support\Facades\DB::table('applications')
                ->where('trainee_id', $trainee->id)
                ->latest()
                ->first();

            if ($latestApp) {
                \Illuminate\Support\Facades\DB::table('trainees')
                    ->where('id', $trainee->id)
                    ->update([
                        'institution_id' => $latestApp->institution_id,
                        'college_id' => $latestApp->college_id,
                        'major_id' => $latestApp->major_id,
                        'university_number' => $latestApp->university_number,
                        'training_hours' => $latestApp->training_hours,
                    ]);
            }
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['college_id']);
            $table->dropForeign(['major_id']);
            $table->dropColumn(['institution_id', 'college_id', 'major_id', 'training_hours']);
            // university_number might have existed before? If so, we shouldn't drop it blindly. 
            // But based on previous analysis, it was in fillable, so likely existed. 
            // However, strictly speaking this migration added it effectively as a "new" source of truth.
        });
    }
};
