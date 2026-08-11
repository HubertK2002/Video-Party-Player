<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(private string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage())
            ->subject('Reset hasła — '.config('app.name'))
            ->greeting('Cześć '.$notifiable->name.'!')
            ->line('Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta.')
            ->action('Ustaw nowe hasło', $url)
            ->line('Link wygasa za '.$minutes.' minut.')
            ->line('Jeśli to nie Ty prosiłeś o reset hasła, nie musisz nic robić — Twoje hasło pozostaje bez zmian.')
            ->salutation('Do zobaczenia, zespół '.config('app.name'));
    }
}
