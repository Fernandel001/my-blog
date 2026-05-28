<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de connexion — The Hacker Experiment')
            ->greeting('Bonjour,')
            ->line('Voici votre code de connexion à 4 chiffres :')
            ->line('## ' . $this->code)
            ->line('Ce code est valable **15 minutes**.')
            ->line("Si vous n'avez pas demandé ce code, ignorez cet email.")
            ->salutation('— The Hacker Experiment');
    }
}
