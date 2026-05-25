<?php

namespace App\Services\Notifications;

use App\Contracts\Notifications\PaymentProofNotifier;
use App\Mail\Transactional\PaymentConfirmationSubmittedMail;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Mail;

class MailPaymentProofNotifier implements PaymentProofNotifier
{
    public function landlordNotifiedOfSubmission(PaymentProof $proof): void
    {
        $proof->loadMissing(['tenant.landlord', 'paymentHistory']);

        $landlord = $proof->tenant->landlord;

        if (! filled($landlord->email)) {
            return;
        }

        Mail::to($landlord)->send(new PaymentConfirmationSubmittedMail($proof));
    }

    public function tenantNotifiedOfReview(PaymentProof $proof): void
    {
        // Tenant portal review notifications — future phase.
    }
}
