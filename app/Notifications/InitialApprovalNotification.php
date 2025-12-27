<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Application;
use Filament\Notifications\Notification as FilamentNotification;

class InitialApprovalNotification extends Notification
{
    protected Application $application;

    public function __construct(Application $application)
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
        $trainingType = $this->application->training_type === Application::TRAINING_TYPE_UNIVERSITY ? 'تدريب جامعي' : 'مزاولة مهنة';
        $adminName = optional($this->application->administrative)->title ?? 'غير محدد';
        $sectionName = optional($this->application->section)->name_location ?? 'غير محدد';

        return FilamentNotification::make()
            ->title('موافقة مبدئية - بانتظار تأكيدك')
            ->body("المتدرب: {$traineeName}\nنوع التدريب: {$trainingType}\nمكان التدريب: {$adminName} - {$sectionName}\nيرجى مراجعة الطلب للتأكيد النهائي.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('info')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(\App\Filament\Resources\ApplicationResource::getUrl('view', ['record' => $this->application])),
            ])
            ->getDatabaseMessage();
    }
}
