<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Applications;
use Filament\Notifications\Notification as FilamentNotification;

class ApplicationCreated extends Notification
{
    // Note: Queueable removed - notifications are sent synchronously
    // Add "use Illuminate\Bus\Queueable;" and "use Queueable;" if you want async processing

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
        $departmentName = optional($this->application->department)->title
            ?? optional($this->application->department)->name_location
            ?? 'غير محدد';
        $sectionName = optional($this->application->section)->name_location ?? 'غير محدد';
        $adminName = optional($this->application->administrative)->title ?? 'غير محدد';

        return FilamentNotification::make()
            ->title('طلب تدريب جديد')
            ->body("المتدرب: {$traineeName}\nالإدارة: {$adminName}\nالقسم: {$sectionName}\nالدائرة: {$departmentName}")
            ->icon('heroicon-o-document-plus')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(\App\Filament\Resources\Applications\ApplicationsResource::getUrl('view', ['record' => $this->application]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New application submitted')
            ->line('A new application has been submitted and requires your attention.')
            ->action('View application', url('/admin/applications/' . $this->application->id));
    }
}

