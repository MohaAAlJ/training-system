<?php

namespace App\Filament\Resources\Mailboxes\Pages;

use App\Filament\Resources\Mailboxes\MailboxResource;
use App\Jobs\DistributeMessageJob;
use App\Models\Mailbox;
use Filament\Resources\Pages\CreateRecord;

class CreateMailbox extends CreateRecord
{
    protected static string $resource = MailboxResource::class;

    protected function afterCreate(): void
    {
        $mailbox = $this->record;

        // Resolve recipients using the model's targeting logic (all, roles, or individual)
        $userIds = $mailbox->resolveRecipientQuery()->pluck('id')->toArray();

        if (!empty($userIds)) {
            // Mark as Draft immediately so the UI reflects it
            $mailbox->update(['status' => Mailbox::STATUS_DRAFT]);

            // Dispatch the job to attach recipients in the background
            DistributeMessageJob::dispatch($mailbox, $userIds);
        }
        // If no users resolved (e.g. no active users for the role), stays as Draft
    }
}
