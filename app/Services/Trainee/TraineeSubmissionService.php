<?php

namespace App\Services\Trainee;

use App\Models\Trainee;
use App\Models\Application;
use App\Models\Major;
use App\Models\Section;
use App\Settings\TrainingSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class TraineeSubmissionService
{
    /**
     * Submit a new trainee application.
     *
     * Handles the complex logic of:
     * 1. Verifying section capacity (concurrency check)
     * 2. Inferring College ID from Major/Institution if missing
     * 3. Creating/Updating the Trainee record
     * 4. Creating the Application record
     * 5. Uploading and linking the application letter file
     *
     * @param array $data Form data including personal and training details
     * @param ?UploadedFile $letterFile Uploaded letter file (optional)
     * @param bool $isExistingTrainee Whether this is a new or existing trainee
     * @param array $originalTraineeData Trusted original data for existing trainees (security)
     * @return Application The created application
     * @throws Exception If section is full or database transaction fails
     */
    public function submitApplication(array $data, ?UploadedFile $letterFile, bool $isExistingTrainee, array $originalTraineeData): Application
    {
        $settings = app(TrainingSettings::class);

        // Verify section capacity one last time
        $section = Section::find($data['sectionId']);
        if ($section && $section->isFull()) {
            if (!$settings->hide_full_sections) {
                throw new Exception('نعتذر، هذا القسم ممتلئ حالياً. يرجى اختيار قسم آخر.');
            }
        }

        // SECURITY FIX: Prevent tampering with locked fields via "Inspect Element"
        if ($isExistingTrainee && !empty($originalTraineeData)) {
            $data['fullName'] = $originalTraineeData['full_name'] ?? $data['fullName'];
            $data['nationalId'] = $originalTraineeData['national_id'] ?? $data['nationalId'];

            if (isset($originalTraineeData['dob'])) {
                $data['dob'] = \Carbon\Carbon::parse($originalTraineeData['dob'])->timezone(config('app.timezone'))->format('Y-m-d');
            }
        }

        $application = DB::transaction(function () use ($data, $letterFile) {
            // 1. Infer college_id if missing
            $collegeId = $data['collegeId'] ?? null;
            $institutionId = $data['institutionId'] ?? null;

            // 1. Infer college_id if missing — only relevant for UNIVERSITY type
            $isPractice    = (int) $data['trainingType'] === Application::PRACTICE;
            $collegeId     = $isPractice ? null : ($data['collegeId'] ?? null);
            $institutionId = $isPractice ? null : ($data['institutionId'] ?? null);

            if (!$isPractice && empty($collegeId) && !empty($data['majorId'])) {
                $major = Major::find($data['majorId']);
                if ($major) {
                    $firstCollege = $major->colleges()->first();
                    if ($firstCollege) {
                        $collegeId    = $firstCollege->id;
                        $institutionId = $firstCollege->institution_id ?? $institutionId;
                    }
                }
            }
            $trainee = Trainee::updateOrCreate(
                ['national_id' => $data['nationalId']],
                [
                    'full_name' => $data['fullName'],
                    'phone_number' => $data['phoneNumber'],
                    'dob' => $data['dob'],
                    'gender' => $data['gender'],
                    'governorate_id' => $data['governorateId'],
                    'street' => $data['street'],
                ]
            );

            // 3. Create application record
            $application = Application::create([
                'trainee_id'       => $trainee->id,
                'section_id'       => $data['sectionId'],
                'street'           => $data['street'],
                'university_number' => $isPractice ? null : ($data['universityNumber'] ?: null),
                'institution_id'   => $institutionId ?: null,
                'college_id'       => $collegeId ?: null,
                'major_id'         => $isPractice ? null : ($data['majorId'] ?: null),
                'training_hours'   => $data['trainingHours'],
                'training_type'    => $data['trainingType'],
                'status'           => Application::STATUS_NEW,
            ]);

            // 4. Handle file upload (if present)
            if ($letterFile) {
                $extension = $letterFile->getClientOriginalExtension();
                $customFileName = "{$application->id}_{$trainee->id}.{$extension}";
                $letterPath = $letterFile->storeAs('application-letters', $customFileName, 'public');

                $application->update(['application_letter' => $letterPath]);
            }

            return $application;
        });

        // Invalidate cache to ensure subsequent checks see the new application
        app(\App\Services\Application\ApplicationStatusService::class)->invalidateCache(
            $data['nationalId'],
            $data['trainingType'],
            $data['dob']
        );

        return $application;
    }

    /**
     * Update an existing STATUS_NEW application.
     *
     * Updates the application record and syncs trainee contact fields.
     * Throws if the application is not found or is no longer STATUS_NEW.
     *
     * @throws Exception
     */
    public function updateApplication(int $applicationId, array $data, mixed $letterFile): Application
    {
        $application = Application::findOrFail($applicationId);

        if (!in_array($application->status, [
            Application::STATUS_NEW,
            Application::STATUS_INITIAL_APPROVE,
        ], true)) {
            throw new Exception('لا يمكن تعديل هذا الطلب في وضعه الحالي.');
        }

        $application = DB::transaction(function () use ($application, $data, $letterFile) {
            // Infer college_id if missing — only relevant for UNIVERSITY type
            $isPractice    = (int) $data['trainingType'] === Application::PRACTICE;
            $collegeId     = $isPractice ? null : ($data['collegeId'] ?? null);
            $institutionId = $isPractice ? null : ($data['institutionId'] ?? null);

            if (!$isPractice && empty($collegeId) && !empty($data['majorId'])) {
                $major = Major::find($data['majorId']);
                if ($major) {
                    $firstCollege = $major->colleges()->first();
                    if ($firstCollege) {
                        $collegeId    = $firstCollege->id;
                        $institutionId = $firstCollege->institution_id ?? $institutionId;
                    }
                }
            }

            // Sync trainee fields including gender
            $trainee = $application->trainee;
            $trainee->update([
                'phone_number'   => $data['phoneNumber'],
                'governorate_id' => $data['governorateId'],
                'street'         => $data['street'],
                'gender'         => $data['gender'],
            ]);

            // Update application fields — always reset status to NEW for re-review
            $application->update([
                'section_id'        => $data['sectionId'],
                'street'            => $data['street'],
                'university_number' => $isPractice ? null : ($data['universityNumber'] ?: null),
                'institution_id'    => $institutionId ?: null,
                'college_id'        => $collegeId ?: null,
                'major_id'          => $isPractice ? null : ($data['majorId'] ?: null),
                'training_hours'    => $data['trainingHours'],
                'training_type'     => $data['trainingType'],
                'status'            => Application::STATUS_NEW,
                'accepted_at'       => null,
            ]);

            // Handle file upload replacement
            if ($letterFile) {
                $extension      = $letterFile->getClientOriginalExtension();
                $customFileName = "{$application->id}_{$trainee->id}.{$extension}";
                $letterPath     = $letterFile->storeAs('application-letters', $customFileName, 'public');
                $application->update(['application_letter' => $letterPath]);
            }

            return $application->fresh();
        });

        // Invalidate status cache so subsequent checks see the updated application
        app(\App\Services\Application\ApplicationStatusService::class)->invalidateCache(
            $data['nationalId'],
            $data['trainingType'],
            $data['dob']
        );

        return $application;
    }
}
