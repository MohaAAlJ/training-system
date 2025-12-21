<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Applications;

class ApplicationCreated extends Notification
{
    use Queueable;

    protected Applications $application;

    public function __construct(Applications $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'تمت إضافة طلب تدريب',
            'application_id' => $this->application->id,
            'trainee_name' => optional($this->application->trainee)->full_name,
            'department' => optional($this->application->department)->title ?? optional($this->application->department)->name_location,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New application submitted')
            ->line('A new application has been submitted and requires your attention.')
            ->action('View application', url('/admin/applications/' . $this->application->id));
    }
}
