<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Applications;
use Filament\Notifications\Notification as FilamentNotification;
use App\Filament\Resources\Applications\ApplicationsResource;

class ApplicationConfirmedNotification extends Notification
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
        $instName = optional($this->application->trainee?->institution)->name ?? 'غير محدد';

        return FilamentNotification::make()
            ->title('تم تأكيد طلب التدريب')
            ->body("المتدرب {$traineeName} ({$instName}) تم تأكيده من قبل الجهة المعنية لتدريب في قسم {$sectionName}. الطلب بانتظار الاعتماد النهائي.")
            ->icon('heroicon-o-check-badge')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(ApplicationsResource::getUrl('view', ['record' => $this->application])),
            ])
            ->getDatabaseMessage();
    }
}
