<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Application;
use App\Models\Trainee;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckApplicationStatus implements ShouldQueue
{
    use Queueable;

    protected string $nationalId;
    protected int $trainingType;

    /**
     * Create a new job instance.
     */
    public function __construct(string $nationalId, int $trainingType)
    {
        $this->nationalId = $nationalId;
        $this->trainingType = $trainingType;
    }

    /**
     * Execute the job - check application status and cache result for 5 minutes
     */
    public function handle(): void
    {
        try {
            // Create cache key
            $cacheKey = "app_status:{$this->nationalId}:{$this->trainingType}";

            // Find trainee by national ID
            $trainee = Trainee::where('national_id', $this->nationalId)->first();

            if (!$trainee) {
                // Cache negative result for 5 minutes
                Cache::put($cacheKey, [
                    'has_application' => false,
                    'trainee' => null,
                    'status' => null,
                ], now()->addMinutes(5));
                return;
            }

            // Check settings for re-application policy
            $settings = \App\Models\GeneralSetting::instance();
            $canReapply = ($this->trainingType == Application::TRAINING_TYPE_UNIVERSITY)
                ? $settings->can_university_reapply
                : $settings->can_practice_reapply;

            // Build query for existing applications
            $query = Application::where('trainee_id', $trainee->id)
                ->where('training_type', $this->trainingType)
                ->whereNull('deleted_at');

            if ($canReapply) {
                // If re-application allowed, only block if there's an application NOT in Ended status
                $blockingApplication = $query->where('status', '!=', Application::STATUS_ENDED_TRAINING)->first();
            } else {
                // If NOT allowed, block if ANY application exists
                $blockingApplication = $query->first();
            }

            // Prepare result
            if ($blockingApplication) {
                $result = [
                    'has_application' => true,
                    'trainee' => $trainee,
                    'status' => $blockingApplication->status,
                    'message' => $this->getApplicationStatusMessage($blockingApplication),
                    'can_continue' => false
                ];
            } else {
                $result = [
                    'has_application' => false,
                    'trainee' => $trainee,
                    'status' => null,
                ];
            }

            // Cache result for 5 minutes
            Cache::put($cacheKey, $result, now()->addMinutes(5));

            Log::info('Application status checked and cached', [
                'national_id' => $this->nationalId,
                'training_type' => $this->trainingType,
                'has_application' => $result['has_application'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking application status in job', [
                'national_id' => $this->nationalId,
                'training_type' => $this->trainingType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get user-friendly message for application status
     */
    private function getApplicationStatusMessage(Application $application): string
    {
        return match ($application->status) {
            Application::STATUS_NEW => 'لديك طلب قيد الانتظار',
            Application::STATUS_INITIAL_APPROVE => 'لديك طلب في انتظار القبول الجامعي',
            Application::STATUS_CONFIRMATION => 'لديك طلب في انتظار التأكيد',
            Application::STATUS_WAITING_LIST => 'لديك طلب في قائمة الانتظار',
            Application::STATUS_STARTED_TRAINING => 'لديك تدريب نشط',
            Application::STATUS_ENDED_TRAINING => 'لديك طلب منتهي',
            Application::STATUS_REJECTED => 'لديك طلب سابق لايمكنك اصادر طلب جديد',
            Application::STATUS_DROPPED => 'لديك طلب منسحب',
            default => 'لديك طلب قائم',
        };
    }
}
