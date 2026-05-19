<?php

namespace App\Jobs;

use App\Models\Mailbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DistributeMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Number of times the job may be attempted. */
    public int $tries = 3;

    /** Number of seconds the job can run before timing out. */
    public int $timeout = 60;

    public function __construct(
        public Mailbox $mailbox,
        public array $userIds,
    ) {}

    public function handle(): void
    {
        // Attach recipients in chunks to avoid memory issues with large user sets
        collect($this->userIds)
            ->chunk(10)
            ->each(fn($chunk) => $this->mailbox->recipients()->syncWithoutDetaching($chunk->toArray()));
    }
}
