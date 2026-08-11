<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestMail extends Command
{
    protected $signature = 'mail:test {email : Adres, na który wysłać wiadomość testową}';

    protected $description = 'Wysyła testową wiadomość, żeby sprawdzić konfigurację poczty';

    public function handle(): int
    {
        $email = $this->argument('email');

        $this->line('Mailer: <info>'.config('mail.default').'</info>');
        $this->line('Nadawca: <info>'.config('mail.from.address').'</info>');

        try {
            Mail::raw(
                'Jeśli to czytasz, wysyłka maili z '.config('app.name').' działa poprawnie.',
                fn ($message) => $message->to($email)->subject('Test wysyłki — '.config('app.name'))
            );
        } catch (Throwable $e) {
            $this->error('Nie udało się wysłać: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Wysłano na '.$email.'.');

        return self::SUCCESS;
    }
}
