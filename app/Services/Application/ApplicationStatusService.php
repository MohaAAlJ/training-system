<?php

declare(strict_types=1);

namespace App\Services\Application;

// use App\Enums\ApplicationStatus;
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
                Application::STATUS_DROPPED,
            ])
            ->first();

        if ($activeBlockingApp) {
            $message = Application::getStatusMessage($activeBlockingApp->status, $activeBlockingApp->training_type);

            return [
                'can_apply' => false,
                'has_application' => true,
                'blocking_application' => $activeBlockingApp,
                // Pass null to message logic if it's a generic block to get generic or specific message
                'message' => $message,
            ];
        }

        // 2. TYPED CHECK: Check re-application policy for TERMINAL applications of the SAME type
        // If we reached here, the trainee has NO active applications (only ended/rejected ones)
        if (!$canReapply) {
            // If re-application is NOT allowed, block if there is an existing application of SAME TYPE
            // (We only check same type because finishing Uni shouldn't block Practice if active list is clear)
            $terminalBlockingApp = Application::where('trainee_id', $trainee->id)
                ->where('training_type', $trainingType)
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
     * Check if trainee data can be prefilled
     */
    public function getTraineeDataForPrefill(string $nationalId): ?array
    {
        $trainee = Trainee::where('national_id', '=', $nationalId)->first();

        return $trainee ? $trainee->toArray() : null;
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
    public function invalidateCache(string $nationalId, int $trainingType): void
    {
        // Invalidate multiple cache keys for different DOBs
        // Since we don't know the DOB, we clear a pattern (if Redis is available)
        Cache::tags(['application_status'])->flush();
    }
}
