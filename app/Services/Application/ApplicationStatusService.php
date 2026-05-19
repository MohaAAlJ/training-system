<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Trainee;
use App\Settings\TrainingSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ApplicationStatusService
 *
 * Handles checking and determining application status for trainees.
 * Centralizes logic for blocking existing applications and determining eligibility.
 *
 * Responsibilities:
 * - Check if trainee can submit a new application
 * - Determine blocking applications based on training type
 * - Generate user-facing status messages
 * - Cache status results for performance
 */
class ApplicationStatusService
{
    private const CACHE_TTL_MINUTES = 5;

    public function __construct(private TrainingSettings $settings) {}

    /**
     * Check if trainee can submit a new application
     *
     * @return array{can_apply: bool, blocking_application: ?Application, message: string}
     */
    public function checkApplicationEligibility(
        string $nationalId,
        int $trainingType,
        string $dob
    ): array {
        if (strlen($nationalId) !== 9) {
            return [
                'can_apply' => false,
                'blocking_application' => null,
                'message' => 'بيانات غير صالحة',
            ];
        }

        // Try to get from cache first
        $cacheKey = $this->getCacheKey($nationalId, $trainingType, $dob);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            Log::debug('Using cached application status', [
                'nationalId' => $nationalId,
                'trainingType' => $trainingType,
            ]);

            return $cached;
        }

        // Check database
        $trainee = Trainee::where('national_id', '=', $nationalId)->first();

        if (!$trainee) {
            return [
                'can_apply' => true,
                'blocking_application' => null,
                'message' => '',
            ];
        }

        // Verify DOB matches
        if ($trainee->dob instanceof Carbon) {
            $traineeDoB = $trainee->dob->format('Y-m-d');
        } else {
            $traineeDoB = (new Carbon($trainee->dob))->format('Y-m-d');
        }

        if ($traineeDoB !== $dob) {
            Log::warning('Trainee DOB mismatch', [
                'traineeId' => $trainee->id,
                'providedDob' => $dob,
                'actualDob' => $traineeDoB,
            ]);

            return [
                'can_apply' => false,
                'blocking_application' => null,
                'message' => 'بيانات التحقق غير مطابقة للسجلات',
            ];
        }

        // Check for existing applications
        $canReapply = $this->canReapply($trainingType);

        Log::info("Check Eligibility: ID={$trainee->id}, Type={$trainingType}, CanReapply=" . ($canReapply ? 'Y' : 'N'));


        // 1. GLOBAL CHECK: Block if there's any ACTIVE application (not terminal) regardless of type
        // This prevents having two concurrent applications
        $activeBlockingApp = Application::where('trainee_id', $trainee->id)
            ->whereNull('deleted_at')
            ->whereNotIn('status', [
                Application::STATUS_ENDED_TRAINING,
                Application::STATUS_REJECTED,
                Application::STATUS_CANCELLED,
            ])
            ->first();

        if ($activeBlockingApp) {
            // STATUS_NEW and STATUS_INITIAL_APPROVE applications can be edited via the public form.
            // Editing an initial-approved application resets it back to STATUS_NEW for re-review.
            $editableStatuses = [
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
            ];

            if (in_array($activeBlockingApp->status, $editableStatuses, true)) {
                return [
                    'can_apply'            => false,
                    'has_application'      => true,
                    'is_new_application'   => true,
                    'blocking_application' => $activeBlockingApp,
                    'message'              => 'لديك طلب يمكن تعديله.',
                    'application_data'     => $this->getApplicationDataForEdit($activeBlockingApp),
                    'trainee_data'         => $this->getTraineeDataForPrefill($nationalId),
                ];
            }

            $message = Application::getStatusMessage($activeBlockingApp->status, $activeBlockingApp->training_type);

            return [
                'can_apply'            => false,
                'has_application'      => true,
                'blocking_application' => $activeBlockingApp,
                'message'              => $message,
            ];
        }

        // 2. TYPED CHECK: Check re-application policy for TERMINAL applications of the SAME type
        // If we reached here, the trainee has NO active applications (only ended/rejected/cancelled ones)
        if (!$canReapply) {
            // If re-application is NOT allowed, block if there is an existing application of SAME TYPE
            // (We only check same type because finishing Uni shouldn't block Practice if active list is clear)
            // Block on REJECTED or CANCELLED applications
            $terminalBlockingApp = Application::where('trainee_id', $trainee->id)
                ->where('training_type', $trainingType)
                ->whereIn('status', [Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
                ->whereNull('deleted_at')
                ->first();

            if ($terminalBlockingApp) {
                // Determine message based on status (likely Ended or Rejected)
                $message = Application::getStatusMessage($terminalBlockingApp->status, $trainingType);

                return [
                    'can_apply' => false,
                    'has_application' => true, // It is an application, just a historical one blocking new entry
                    'blocking_application' => $terminalBlockingApp,
                    'message' => $message,
                ];
            }
        }



        // No blocking application found
        $blockingApplication = null;

        Log::debug('No blocking application found', [
            'traineeId' => $trainee->id,
            'trainingType' => $trainingType,
        ]);

        $result = [
            'can_apply' => true,
            'has_application' => false,
            'blocking_application' => null,
            'message' => '',
            'trainee_data' => $this->getTraineeDataForPrefill($nationalId),
        ];

        // Cache the result
        Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $result;
    }

    /**
     * Check if a specific trainee ID is eligible for a new application.
     * Used by internal forms (MOH, College, Admin) where DOB verification is not required.
     *
     * @return array{can_apply: bool, message: string}
     */
    public function checkEligibilityByTraineeId(int $traineeId, ?int $trainingType, ?int $ignoredApplicationId = null): array
    {
        Log::info("Internal Check Eligibility: ID={$traineeId}, Type=" . ($trainingType ?? 'null') . ", CanReapply=" . ($trainingType !== null ? ($this->canReapply($trainingType) ? 'Y' : 'N') : 'admin-skip'));

        // 1. GLOBAL CHECK: Block if there's any ACTIVE application (not terminal) regardless of type
        $activeBlockingAppQuery = Application::where('trainee_id', $traineeId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', [
                Application::STATUS_ENDED_TRAINING,
                Application::STATUS_REJECTED,
                Application::STATUS_CANCELLED,
            ]);

        if ($ignoredApplicationId) {
            $activeBlockingAppQuery->where('id', '!=', $ignoredApplicationId);
        }

        $activeBlockingApp = $activeBlockingAppQuery->first();

        if ($activeBlockingApp) {
            $message = Application::getStatusMessage($activeBlockingApp->status, $activeBlockingApp->training_type);

            return [
                'can_apply' => false,
                'message'   => $message ?: 'الطالب لديه طلب نشط بالفعل في النظام.',
            ];
        }

        // 2. TYPED CHECK: Only run if training type is known and re-application is NOT allowed.
        // When $trainingType is null (Admin), skip this entirely — they manage settings themselves.
        if ($trainingType !== null && !$this->canReapply($trainingType)) {
            $terminalBlockingAppQuery = Application::where('trainee_id', $traineeId)
                ->where('training_type', $trainingType)
                ->whereIn('status', [Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
                ->whereNull('deleted_at');

            if ($ignoredApplicationId) {
                $terminalBlockingAppQuery->where('id', '!=', $ignoredApplicationId);
            }

            $terminalBlockingApp = $terminalBlockingAppQuery->first();

            if ($terminalBlockingApp) {
                $message = Application::getStatusMessage($terminalBlockingApp->status, $trainingType);

                return [
                    'can_apply' => false,
                    'message'   => $message ?: 'الطالب لديه طلب سابق من نفس النوع، ولا يسمح النظام بتقديم طلبات متعددة من هذا النوع.',
                ];
            }
        }

        return [
            'can_apply' => true,
            'message'   => '',
        ];
    }

    /**
     * Extract editable fields from an existing STATUS_NEW / STATUS_INITIAL_APPROVE
     * application for public form prefill.
     */
    private function getApplicationDataForEdit(Application $app): array
    {
        $section = $app->section;
        $departmentId = $section?->departments?->first()?->id;
        $administrativeId = $section?->administrative_id;

        return [
            'id'                 => $app->id,
            'training_type'      => $app->training_type,
            'section_id'         => $app->section_id ?? $section?->id,
            'administrative_id'  => $administrativeId ?? $app->administrative_id,
            'department_id'      => $departmentId ?? $app->department_id,
            'institution_id'     => $app->institution_id,
            'college_id'         => $app->college_id,
            'major_id'           => $app->major_id,
            'university_number'  => $app->university_number,
            'training_hours'     => $app->training_hours,
            'street'             => $app->street,
            'tags'               => $app->tags,
            'application_letter' => $app->application_letter,
        ];
    }

    /**
     * Check if trainee data can be prefilled
     */
    public function getTraineeDataForPrefill(string $nationalId): ?array
    {
        $trainee = Trainee::where('national_id', '=', $nationalId)->first();

        if (!$trainee) {
            return null;
        }

        $data = $trainee->toArray();

        // Fetch the latest application to get educational data
        $latestApplication = Application::where('trainee_id', $trainee->id)
            ->latest()
            ->first();

        if ($latestApplication) {
            $latestSection = $latestApplication->section;
            $data['administrative_id'] = $latestSection?->administrative_id ?? $latestApplication->administrative_id;
            $data['department_id'] = $latestSection?->departments?->first()?->id ?? $latestApplication->department_id;
            $data['section_id'] = $latestApplication->section_id;
            $data['institution_id'] = $latestApplication->institution_id;
            $data['college_id'] = $latestApplication->college_id;
            $data['major_id'] = $latestApplication->major_id;
            $data['university_number'] = $latestApplication->university_number;
            $data['training_hours'] = $latestApplication->training_hours;
        }

        return $data;
    }

    /**
     * Determine if re-application is allowed for training type
     */
    private function canReapply(int $trainingType): bool
    {
        return match ($trainingType) {
            Application::UNIVERSITY => (bool) $this->settings->can_university_reapply,
            Application::PRACTICE => (bool) $this->settings->can_practice_reapply,
            default => false,
        };
    }

    /**
     * Generate cache key for application status
     */
    private function getCacheKey(string $nationalId, int $trainingType, string $dob): string
    {
        $canReapply = $this->canReapply($trainingType);

        return "app_status:{$nationalId}:{$trainingType}:{$dob}:" . ($canReapply ? '1' : '0');
    }

    /**
     * Invalidate cache for a specific trainee
     */
    public function invalidateCache(string $nationalId, int $trainingType, string $dob): void
    {
        $key = $this->getCacheKey($nationalId, $trainingType, $dob);
        Cache::forget($key);
    }
}
