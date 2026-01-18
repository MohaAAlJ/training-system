<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\TrainingType;
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
        $trainee = Trainee::where('national_id', $nationalId)->first();

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
        $trainingTypeEnum = TrainingType::from($trainingType);
        $canReapply = $this->canReapply($trainingTypeEnum);

        $query = Application::where('trainee_id', $trainee->id)
            ->where('training_type', $trainingType)
            ->whereNull('deleted_at');

        if ($canReapply) {
            // Block if there's an active application (not ended)
            $blockingApplication = $query
                ->where('status', '!=', ApplicationStatus::ENDED_TRAINING->value)
                ->first();
        } else {
            // Block any existing application
            $blockingApplication = $query->first();
        }

        // Prepare result
        if ($blockingApplication) {
            Log::info('Blocking application found', [
                'applicationId' => $blockingApplication->id,
                'status' => $blockingApplication->status,
            ]);

            $result = [
                'can_apply' => false,
                'blocking_application' => $blockingApplication,
                'message' => ApplicationStatus::from($blockingApplication->status)
                    ->message($trainingType),
            ];
        } else {
            Log::debug('No blocking application found', [
                'traineeId' => $trainee->id,
                'trainingType' => $trainingType,
            ]);

            $result = [
                'can_apply' => true,
                'blocking_application' => null,
                'message' => '',
            ];
        }

        // Cache the result
        Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $result;
    }

    /**
     * Check if trainee data can be prefilled
     */
    public function getTraineeDataForPrefill(string $nationalId): ?array
    {
        $trainee = Trainee::where('national_id', $nationalId)->first();

        return $trainee ? $trainee->toArray() : null;
    }

    /**
     * Determine if re-application is allowed for training type
     */
    private function canReapply(TrainingType $trainingType): bool
    {
        return match ($trainingType) {
            TrainingType::UNIVERSITY => (bool) $this->settings->can_university_reapply,
            TrainingType::PRACTICE => (bool) $this->settings->can_practice_reapply,
        };
    }

    /**
     * Generate cache key for application status
     */
    private function getCacheKey(string $nationalId, int $trainingType, string $dob): string
    {
        $canReapply = $this->canReapply(TrainingType::from($trainingType));

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
