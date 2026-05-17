<?php

namespace App\Console\Commands;

use App\Mail\Auth\VerifyEmailMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendTestMailCommand extends Command
{
    protected $signature = 'mail:test {email : Recipient address}';

    protected $description = 'Send a test verification-style email using the configured mailer';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => 1, 'hash' => sha1($email)],
        );

        try {
            Mail::to($email)->send(new VerifyEmailMail($url, 'Test User'));

            $this->info("Test email sent to {$email} via mailer: ".config('mail.default'));

            if (config('mail.default') === 'failover') {
                $this->warn('failover uses Mailpit then log — check http://127.0.0.1:8025 or storage/logs/laravel.log');
            }

            if (config('mail.default') === 'mailpit') {
                $this->warn('mailpit does not deliver to real inboxes — open http://127.0.0.1:8025');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
