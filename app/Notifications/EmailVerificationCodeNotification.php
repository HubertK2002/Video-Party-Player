<?php

namespace App\Notifications;

use App\Models\EmailVerificationCode;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCodeNotification extends Notification
{
    public function __construct(private string $code)
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
        return (new MailMessage())
            ->subject('Twój kod weryfikacyjny — '.config('app.name'))
            ->greeting('Cześć '.$notifiable->name.'!')
            ->line('Twój kod weryfikacyjny do dokończenia rejestracji:')
            ->line('**'.$this->code.'**')
            ->line('Kod jest ważny przez '.EmailVerificationCode::TTL_MINUTES.' minut.')
            ->line('Jeśli to nie Ty zakładałeś konto, zignoruj tę wiadomość.')
            ->salutation('Do zobaczenia, zespół '.config('app.name'));
    }
}
