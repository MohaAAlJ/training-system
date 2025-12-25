<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EndTrainingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:end-training';

    protected $description = 'Automatically end training for Application whose end date has passed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\Application::where('status', \App\Models\Application::STATUS_STARTED_TRAINING)
            ->whereDate('end_date', '<=', now())
            ->update(['status' => \App\Models\Application::STATUS_ENDED_TRAINING]);

        $this->info("Successfully ended training for $count Application.");
    }
}
