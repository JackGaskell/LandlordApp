<?php

namespace App\Mail;

use App\Mail\Concerns\UsesLandlordMailTheme;
use App\Services\Mail\MailEnvelopeFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base mailable for all application email (reminders, auth, transactional).
 */
abstract class LandlordMailable extends Mailable
{
    use Queueable;
    use SerializesModels;
    use UsesLandlordMailTheme;

    protected function transactionalEnvelope(string $subject, ?string $fromName = null): Envelope
    {
        return app(MailEnvelopeFactory::class)->transactional($subject, $fromName);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withMailTheme(array $data = []): array
    {
        return array_merge($this->mailTheme(), $data);
    }
}
