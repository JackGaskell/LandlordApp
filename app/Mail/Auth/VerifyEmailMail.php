<?php

namespace App\Mail\Auth;

use App\Mail\LandlordMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyEmailMail extends LandlordMailable
{
    public function __construct(
        public string $verificationUrl,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return $this->transactionalEnvelope('Verify your email address');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.auth.verify-email',
            with: $this->withMailTheme([
                'userName' => $this->userName,
                'verificationUrl' => $this->verificationUrl,
                'preheader' => 'Confirm your email to finish setting up your account.',
                'title' => 'Verify your email',
            ]),
        );
    }
}
