<?php

declare(strict_types=1);

namespace App\Actions\Application;

// use App\Enums\ApplicationStatus;
// use App\Enums\TrainingType;
use App\Models\Application;
use App\Models\Major;
use App\Models\Section;
use App\Models\Trainee;
use App\Services\File\ApplicationLetterUploadService;
use App\Settings\TrainingSettings;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SubmitApplicationAction
 *
 * Handles the complete application submission flow.
 * Encapsulates business logic for creating trainee and application records.
 *
 * Responsibilities:
 * - Validate section capacity
 * - Create or update trainee records
 * - Create application record
 * - Handle file uploads
 * - Maintain data integrity with transactions
 */
class SubmitApplicationAction
{
    public function __construct(
        private ApplicationLetterUploadService $fileService,
        private TrainingSettings $settings
    ) {}

    /**
     * Submit application with full validation and transaction handling
     *
     * @param array<string, mixed> $data Validated application data
     * @param UploadedFile|null $letterFile Optional application letter file
     *
     * @return Application Created application record
     */
    public function execute(array $data, ?UploadedFile $letterFile = null): Application
    {
        // Validate section capacity one final time
        $section = Section::find($data['sectionId']);
        if ($section && ($section->getCapacityStats()['is_full'] ?? false)) {
            if (!$this->settings->hide_full_sections) {
                throw new Exception('نعتذر، هذا القسم ممتلئ حالياً. يرجى اختيار قسم آخر.');
            }
        }

        // Use database transaction to ensure data integrity
        return DB::transaction(function () use ($data, $letterFile) {
            // 1. Infer college_id if missing
            if (empty($data['collegeId']) && !empty($data['majorId'])) {
                $data['collegeId'] = $this->inferCollege($data['majorId'], $data['institutionId']);
            }

            // 2. Create or update trainee record
            $trainee = $this->createOrUpdateTrainee($data);

            // 3. Create application record
            $application = $this->createApplication($trainee, $data);

            // 4. Handle file upload if present
            if ($letterFile) {
                $filePath = $this->fileService->storeApplicationLetter(
                    $letterFile,
                    $application->id,
                    $trainee->id
                );
                $application->update(['application_letter' => $filePath]);
            }

            Log::info('Application submitted successfully', [
                'applicationId' => $application->id,
                'traineeId' => $trainee->id,
                'trainingType' => $data['trainingType'],
            ]);

            return $application;
        });
    }

    /**
     * Create or update trainee record
     */
    private function createOrUpdateTrainee(array $data): Trainee
    {
        return Trainee::updateOrCreate(
            ['national_id' => $data['nationalId']],
            [
                'full_name' => $data['fullName'],
                'phone_number' => $data['phoneNumber'],
                'dob' => $data['dob'],
                'governorate_id' => $data['governorateId'],
                'street' => $data['street'],
                'institution_id' => $data['institutionId'] ?: null,
                'college_id' => $data['collegeId'] ?: null,
                'major_id' => $data['majorId'] ?: null,
                'training_hours' => $data['trainingHours'],
            ]
        );
    }

    /**
     * Create application record
     */
    private function createApplication(Trainee $trainee, array $data): Application
    {
        return Application::create([
            'trainee_id' => $trainee->id,
            'section_id' => $data['sectionId'],
            'street' => $data['street'],
            'training_type' => $data['trainingType'],
            'status' => Application::STATUS_NEW,
        ]);
    }

    /**
     * Infer college from major and institution
     */
    private function inferCollege(int $majorId, int $institutionId): ?int
    {
        try {
            $major = Major::find($majorId);

            if ($major) {
                $college = $major->colleges()
                    ->where('institution_id', $institutionId)
                    ->first();

                return $college?->id;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to infer college', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
