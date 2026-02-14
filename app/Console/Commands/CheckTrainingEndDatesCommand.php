<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTrainingEndDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-training-end-dates';

    protected $description = 'Check applications for upcoming end dates and send notifications.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = app(\App\Settings\TrainingSettings::class);

        // Check if the feature is enabled in General Settings
        if (!$settings->whatsapp_end_training) {
            $this->info('WhatsApp End Training notification is disabled in settings.');
            return;
        }

        $days = $settings->whatsapp_end_training_days;

        // Find applications where end_date is exactly NOW + DAYS (sending the warning X days BEFORE it ends)
        $targetDate = now()->addDays($days)->format('Y-m-d');

        $applications = \App\Models\Application::where('status', \App\Models\Application::STATUS_STARTED_TRAINING)
            ->whereDate('end_date', '=', $targetDate)
            ->with('trainee')
            ->get();

        $service = app(\App\Services\WhatsApp\WhatsAppNotificationService::class);
        $count = 0;

        foreach ($applications as $app) {
            if ($app->trainee?->phone_number) {
                 $msg = $service->getGreeting($app->trainee) . ": " . $app->trainee->full_name . "\n"
                      . "نحيطكم علماً بانتهاء فترة التدريب الخاصة بكم خلال {$days} يوم (بتاريخ {$app->end_date->format('Y-m-d')})" . "\n"
                      . "يرجى تسليم النماذج المطلوبة." . "\n"
                      . "بالتوفيق";

                 $service->sendMessage($app->trainee->phone_number, $msg);
                 $count++;
            }
        }

        $this->info("Sent training reminder to $count trainees.");
    }
}
