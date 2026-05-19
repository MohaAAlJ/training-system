<?php

namespace App\Notifications;

use App\Models\Application;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ApplicationFilesUploadedNotification extends Notification
{
    public function __construct(protected Application $application) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $traineeName = optional($this->application->trainee)->full_name ?? 'غير معروف';
        $nationalId  = optional($this->application->trainee)->national_id ?? '';

        return FilamentNotification::make()
            ->title('ملفات طلب تدريب جاهزة للتحميل')
            ->body("تم رفع ملفات الطلب للمتدرب {$traineeName} ({$nationalId}). يمكنك الآن تحميل الملفات.")
            ->icon('heroicon-o-document-arrow-down')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(ApplicationResource::getUrl('view', ['record' => $this->application->id])),
            ])
            ->getDatabaseMessage();
    }
}
