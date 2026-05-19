<?php

namespace App\Console\Commands;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartWaitingApplicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-waiting-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks applications on waiting list whose start date has arrived, and either starts them or delays them based on section capacity.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $this->info("Checking waiting list applications for {$today}...");

        $applications = Application::where('status', Application::STATUS_WAITING_LIST)
            ->whereDate('start_date', '<=', $today)
            ->with(['section'])
            ->get();

        if ($applications->isEmpty()) {
            $this->info('No applications found waiting to start today.');
            return;
        }

        $startedCount = 0;
        $delayedCount = 0;

        foreach ($applications as $application) {
            /** @var Application $application */
            $section = $application->section;

            // If section doesn't exist or is inactive, skip it
            if (!$section || !$section->active) {
                $this->warn("Application #{$application->id} skipped (Section missing or inactive).");
                continue;
            }

            // $stats = $section->getCapacityStats();
            // $isFull = $stats['is_full'] ?? false;

            if ($section->isFull()) {
                // Section is full. Find the earliest ending application in this section to calculate next available date.
                $earliestEndingApp = Application::where('section_id', $section->id)
                    ->where('status', Application::STATUS_STARTED_TRAINING)
                    ->whereNotNull('end_date')
                    ->orderBy('end_date', 'asc')
                    ->first();

                if ($earliestEndingApp) {
                    $nextStartDate = Carbon::parse($earliestEndingApp->end_date)->addDay();

                    $hours       = (int) ($application->training_hours ?? 0);
                    $dailyHrs    = (int) ($application->days_note['daily_hours'] ?? 6);
                    $selectedDays = array_filter((array) ($application->days_note['training_days'] ?? []), fn($v) => $v !== '' && $v !== null);
                    $daysPerWeek  = count($selectedDays);

                    if ($hours > 0 && $dailyHrs > 0 && $daysPerWeek > 0) {
                        $sessionsNeeded = (int) ceil($hours / $dailyHrs);
                        $calendarDays   = (int) round(($sessionsNeeded / $daysPerWeek) * 7);
                    } else {
                        $calendarDays = 30;
                    }

                    $newEndDate = $nextStartDate->copy()->addDays($calendarDays);

                    $application->update([
                        'start_date' => $nextStartDate->toDateString(),
                        'end_date'   => $newEndDate->toDateString(),
                    ]);

                    $this->info("Application #{$application->id} delayed to {$nextStartDate->toDateString()} because section '{$section->name}' is full.");
                    $delayedCount++;
                } else {
                    // Fallback if no earliest app is found (shouldn't realistically happen if it's full, but just in case)
                    $this->warn("Application #{$application->id} section '{$section->name}' is full but couldn't find active applications. Skipping.");
                }
            } else {
                // Section is not full. Start the training.
                $application->update([
                    'status' => Application::STATUS_STARTED_TRAINING,
                ]);

                $this->info("Application #{$application->id} successfully started in section '{$section->name}'.");
                $startedCount++;
            }
        }

        $this->info("Completed processing. Started: {$startedCount}, Delayed: {$delayedCount}.");
    }
}
