<?php

namespace App\Contracts\Notifications;

use App\Models\PaymentProof;

interface PaymentProofNotifier
{
    public function landlordNotifiedOfSubmission(PaymentProof $proof): void;

    public function tenantNotifiedOfReview(PaymentProof $proof): void;
}
