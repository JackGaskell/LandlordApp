<?php

namespace App\Mail\Transactional;

use App\Mail\LandlordMailable;
use App\Models\PaymentProof;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentConfirmationSubmittedMail extends LandlordMailable
{
    public function __construct(
        public PaymentProof $proof,
    ) {}

    public function envelope(): Envelope
    {
        $tenantName = $this->proof->tenant->name;

        return $this->transactionalEnvelope("{$tenantName} submitted a payment confirmation");
    }

    public function content(): Content
    {
        $proof = $this->proof->loadMissing(['tenant', 'paymentHistory']);
        $tenant = $proof->tenant;
        $payment = $proof->paymentHistory;
        $amount = $payment
            ? number_format((float) $payment->amount, 2)
            : number_format((float) $tenant->rent_amount, 2);

        $period = $payment?->due_date->format('F Y') ?? 'current period';

        return new Content(
            view: 'mail.transactional.payment-confirmation-submitted',
            with: $this->withMailTheme([
                'landlordName' => $tenant->landlord->name,
                'tenantName' => $tenant->name,
                'amount' => $amount,
                'period' => $period,
                'currencySymbol' => config('landlord.mail.currency_symbol'),
                'reviewUrl' => route('payment-proofs.show', $proof),
                'preheader' => "{$tenant->name} uploaded a receipt for {$period} rent ({$amount}).",
                'title' => 'Confirmation to review',
            ]),
        );
    }
}
