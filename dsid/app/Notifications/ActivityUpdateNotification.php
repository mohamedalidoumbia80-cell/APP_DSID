<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Activite $activite,
        private readonly string $subject,
        private readonly string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Bonjour '.$notifiable->first_name)
            ->line($this->message)
            ->line('Activité : '.$this->activite->title)
            ->action('Consulter l’activité', route('activites.show', $this->activite));
    }
}
