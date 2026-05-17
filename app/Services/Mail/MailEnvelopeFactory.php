<?php

namespace App\Services\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Builds consistent envelope metadata for transactional mail.
 */
class MailEnvelopeFactory
{
    public function transactional(string $subject, ?string $fromName = null): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = $fromName ?? config('mail.from.name');

        $envelope = new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $subject,
        );

        $supportAddress = config('landlord.mail.support_address');

        if (filled($supportAddress)) {
            $envelope->replyTo = [new Address($supportAddress, $fromName)];
        }

        return $envelope;
    }
}
