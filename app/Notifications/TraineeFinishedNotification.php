<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Applications;
use Filament\Notifications\Notification as FilamentNotification;
use App\Filament\Resources\Applications\ApplicationsResource;

class TraineeFinishedNotification extends Notification
{
    protected Applications $application;

    public function __construct(Applications $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $traineeName = optional($this->application->trainee)->full_name ?? 'غير معروف';
        $sectionName = optional($this->application->section)->name_location ?? 'غير محدد';

        return FilamentNotification::make()
            ->title('انتهاء تدريب متدرب')
            ->body("المتدرب {$traineeName} أنهى فترة تدريبه في القسم {$sectionName}.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('info')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(ApplicationsResource::getUrl('view', ['record' => $this->application])),
            ])
            ->getDatabaseMessage();
    }
}
