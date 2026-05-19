<?php

namespace App\Filament\Resources\Mailboxes\Pages;

use App\Filament\Resources\Mailboxes\MailboxResource;
use App\Jobs\DistributeMessageJob;
use App\Models\Mailbox;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMailbox extends EditRecord
{
    protected static string $resource = MailboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('إرسال الآن')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === Mailbox::STATUS_DRAFT)
                ->action(function () {
                    $mailbox = $this->record;
                    $userIds = $mailbox->resolveRecipientQuery()->pluck('id')->toArray();

                    if (!empty($userIds)) {
                        DistributeMessageJob::dispatch($mailbox, $userIds);
                    }

                    $mailbox->update(['status' => Mailbox::STATUS_SENT]);

                    Notification::make()
                        ->title('تم إرسال الرسالة بنجاح')
                        ->success()
                        ->send();

                    $this->redirect(MailboxResource::getUrl('index'));
                }),

            ViewAction::make(),

            DeleteAction::make()
                ->visible(fn() => $this->record->status === Mailbox::STATUS_DRAFT),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->visible(fn() => $this->record->status === Mailbox::STATUS_DRAFT);
    }
}
