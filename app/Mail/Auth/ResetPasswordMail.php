<?php

namespace App\Mail\Auth;

use App\Mail\LandlordMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ResetPasswordMail extends LandlordMailable
{
    public function __construct(
        public string $resetUrl,
        public string $userName,
        public int $expireMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return $this->transactionalEnvelope('Reset your password');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.auth.reset-password',
            with: $this->withMailTheme([
                'userName' => $this->userName,
                'resetUrl' => $this->resetUrl,
                'expireMinutes' => $this->expireMinutes,
                'preheader' => 'Use the link below to choose a new password for your account.',
                'title' => 'Reset your password',
            ]),
        );
    }
}
