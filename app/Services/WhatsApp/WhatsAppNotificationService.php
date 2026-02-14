<?php

namespace App\Services\WhatsApp;

// use App\Enums\ApplicationStatus;
use App\Enums\Gender;
use App\Models\Application;
use App\Models\Trainee;
use App\Settings\TrainingSettings;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function __construct(protected TrainingSettings $settings) {}

    /**
     * Handle Application Status Changes
     */
    public function handleApplicationStatusChange(Application $application, int $newStatus)
    {
        $phoneNumber = $application->trainee?->phone_number;

        if (!$phoneNumber) {
            Log::warning("WhatsApp Notification: No phone number for application {$application->id}");
            return;
        }

        switch ($newStatus) {
            // Case 1: Initial Approval
            case Application::STATUS_INITIAL_APPROVE:
                if ($this->settings->whatsapp_initial_approve) {
                    $this->sendInitialApprovalMessage($application, $phoneNumber);
                }
                break;

            // Case 2: Started Training
            case Application::STATUS_STARTED_TRAINING:
                if ($this->settings->whatsapp_start_training) {
                    $this->sendTrainingStartedMessage($application, $phoneNumber);
                }
                break;

            // Case 3: Ended Training
            case Application::STATUS_ENDED_TRAINING:
                // Note: The 'CheckTrainingEndDatesCommand' handles the *Warning* X days before.
                // This block handles the actual *Completion* message when status updates to Ended.
                if ($this->settings->whatsapp_end_training) {
                    $this->sendTrainingEndedMessage($application, $phoneNumber);
                }
                break;
        }
    }

    /**
     * Send a text message via the queued job
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            // Dispatch job to queue with rate limiting
            SendWhatsAppMessageJob::dispatch($to, $message);
            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch WhatsApp message: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Training Started Message
     */
    protected function sendTrainingStartedMessage(Application $application, string $phoneNumber)
    {
        $startDate = $application->start_date ? \Carbon\Carbon::parse($application->start_date)->format('Y-m-d') : 'N/A';
        $adminName = $application->section?->administrative?->name ?? 'إدارة';
        $sectionName = $application->section?->name ?? 'قسم';

        $name = $application->trainee->full_name;
        $greeting = $this->getGreeting($application->trainee);

        $msg = "{$greeting}: {$name}\n"
            . "يبدأ التدريب يوم {$startDate} في {$adminName} - قسم ({$sectionName})\n"
            . "بالتوفيق";

        $this->sendMessage($phoneNumber, $msg);
    }

    /**
     * Send Initial Approval Message
     */
    protected function sendInitialApprovalMessage(Application $application, string $phoneNumber)
    {
        $name = $application->trainee->full_name;
        $greeting = $this->getGreeting($application->trainee);

        $msg = "{$greeting}: {$name}\n"
            . "تمت الموافقة المبدئية على طلب التدريب الخاص بك.\n"
            . "يرجى مراجعة الكلية أو جهة الاختصاص لاستكمال الإجراءات.\n"
            . "بالتوفيق";

        $this->sendMessage($phoneNumber, $msg);
    }

    /**
     * Send Training Ended Message
     */
    protected function sendTrainingEndedMessage(Application $application, string $phoneNumber)
    {
        $name = $application->trainee->full_name;
        $greeting = $this->getGreeting($application->trainee);
        // Assuming end_date exists on Application model similar to start_date
        // Using current date if end_date is null as fallback for immediate notification
        $endDate = $application->end_date ? \Carbon\Carbon::parse($application->end_date)->format('Y-m-d') : now()->format('Y-m-d');

        $msg = "{$greeting}: {$name}\n"
            . "نحيطكم علماً بانتهاء فترة التدريب الخاصة بكم.\n"
            . "نشكر لكم التزامكم ونتمنى لكم التوفيق في حياتكم المهنية.";

        $this->sendMessage($phoneNumber, $msg);
    }

    /**
     * Helper to get gender-appropriate greeting
     */
    public function getGreeting(Trainee $trainee): string
    {
        $gender = $trainee->gender;
        // Check both object enum and value to be safe
        $isFemale = $gender === Gender::FEMALE || $gender === Gender::FEMALE->value;

        return $isFemale ? 'المتدربة' : 'المتدرب';
    }
}
