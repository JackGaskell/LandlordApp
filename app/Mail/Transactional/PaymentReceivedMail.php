<?php

namespace App\Mail\Transactional;

use App\Mail\LandlordMailable;
use App\Models\PaymentHistory;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to the landlord when a rent payment is recorded.
 */
class PaymentReceivedMail extends LandlordMailable
{
    public function __construct(
        public PaymentHistory $payment,
        public string $landlordName,
        public string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        return $this->transactionalEnvelope('Payment recorded for '.$this->tenantName);
    }

    public function content(): Content
    {
        $amount = number_format((float) $this->payment->amount, 2);
        $paidAt = $this->payment->paid_at?->format('j F Y') ?? now()->format('j F Y');

        return new Content(
            view: 'mail.transactional.payment-received',
            with: $this->withMailTheme([
                'landlordName' => $this->landlordName,
                'tenantName' => $this->tenantName,
                'amount' => $amount,
                'currencySymbol' => config('landlord.mail.currency_symbol'),
                'paidAt' => $paidAt,
                'dashboardUrl' => route('dashboard'),
                'preheader' => "A payment of {$amount} was recorded for {$this->tenantName}.",
                'title' => 'Payment recorded',
            ]),
        );
    }
}
