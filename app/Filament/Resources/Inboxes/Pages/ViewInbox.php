<?php

namespace App\Filament\Resources\Inboxes\Pages;

use App\Filament\Resources\Inboxes\InboxResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInbox extends ViewRecord
{
    protected static string $resource = InboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('mark_as_unread')
                ->label('تعيين كغير مقروء')
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->visible(function () {
                    $mailbox = $this->getRecord();
                    $pivot = $mailbox->recipients()->where('user_id', \Illuminate\Support\Facades\Auth::id())->first()?->pivot;
                    // If read_at is not null, the button is visible. Once unread, it vanishes.
                    return $pivot && !is_null($pivot->read_at);
                })
                ->action(function () {
                    $mailbox = $this->getRecord();
                    \Illuminate\Support\Facades\Auth::user()->receivedMessages()->updateExistingPivot($mailbox->id, ['read_at' => null]);

                    \Filament\Notifications\Notification::make()
                        ->title('تم تعيين الرسالة كغير مقروءة')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $mailbox = $this->getRecord();
        $pivot = $mailbox->recipients()->where('user_id', \Illuminate\Support\Facades\Auth::id())->first()?->pivot;

        if ($pivot && is_null($pivot->read_at)) {
            \Illuminate\Support\Facades\Auth::user()->receivedMessages()->updateExistingPivot($mailbox->id, ['read_at' => now()]);
        }
    }
}
