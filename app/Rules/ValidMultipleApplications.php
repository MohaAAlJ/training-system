<?php

namespace App\Rules;

use App\Models\Application;
use App\Models\Trainee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class ValidMultipleApplications implements ValidationRule
{
    public function __construct(
        protected ?int $ignoredApplicationId = null
    ) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $trainee = Trainee::where('national_id', $value)->first();

        if (! $trainee) {
            return;
        }

        if ($this->ignoredApplicationId) {
            $existingApp = Application::find($this->ignoredApplicationId);
            if ($existingApp && $existingApp->trainee_id === $trainee->id) {
                return;
            }
        }

        $applications = Application::where('trainee_id', $trainee->id)->get();

        if ($applications->isEmpty()) {
            return; // No past applications, valid.
        }

        $user = Auth::user();

        if ($user?->isCollegeSupervisor()) {
            $upgradable = Application::where('trainee_id', $trainee->id)
                ->whereIn('status', [
                    Application::STATUS_NEW,
                    Application::STATUS_INITIAL_APPROVE,
                ])->first();

            if ($upgradable) {
                $supervisorCollegeId = $user->college?->id;

                if ($upgradable->college_id !== null && $upgradable->college_id !== $supervisorCollegeId) {
                    $fail('هذا المتدرب لديه طلب تدريب مسجل من كلية أخرى.');
                    return;
                }

                return;
            }
        }
        // ────────────────────────────────────────────────────────────────────────

        // Resolve training type from role.
        if ($user?->isMinistry()) {
            $trainingType = Application::PRACTICE;
        } elseif ($user?->isCollegeSupervisor()) {
            $trainingType = Application::UNIVERSITY;
        } else {
            // Admins and others: only enforce the global active check, not typed history
            $trainingType = null;
        }

        $service = app(\App\Services\Application\ApplicationStatusService::class);
        $result = $service->checkEligibilityByTraineeId($trainee->id, $trainingType, $this->ignoredApplicationId);

        if (!$result['can_apply']) {
            $fail($result['message']);
        }
    }
}
