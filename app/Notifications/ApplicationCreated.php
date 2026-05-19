<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Application;
use Filament\Notifications\Notification as FilamentNotification;

class ApplicationCreated extends Notification
{
    // Note: Queueable removed - notifications are sent synchronously
    // Add "use Illuminate\Bus\Queueable;" and "use Queueable;" if you want async processing

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

        $section = $this->application->section;
        $sectionName = optional($section)->name ?? 'غير محدد';

        $departmentName = $section?->departments->first()?->name ?? 'غير محدد';

        $adminName = optional($section?->administrative)->name ?? 'غير محدد';

        return FilamentNotification::make()
            ->title('طلب تدريب جديد')
            ->body("المتدرب: {$traineeName}\nالإدارة: {$adminName}\nالقسم: {$sectionName}\nالدائرة: {$departmentName}")
            ->icon('heroicon-o-document-plus')
            ->iconColor('success')
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->button()
                    ->url(\App\Filament\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $this->application->id]))
                // ->markAsRead() // markAsRead might not be available on generic Action
                ,
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New application submitted')
            ->line('A new application has been submitted and requires your attention.')
            ->action('View application', url('/admin/Application/' . $this->application->id));
    }
}
