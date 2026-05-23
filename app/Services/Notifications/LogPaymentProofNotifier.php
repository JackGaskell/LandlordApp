<?php

namespace App\Services\Notifications;

use App\Contracts\Notifications\PaymentProofNotifier;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Log;

/**
 * Default notifier — logs until mail/SMS channels are configured.
 */
class LogPaymentProofNotifier implements PaymentProofNotifier
{
    public function landlordNotifiedOfSubmission(PaymentProof $proof): void
    {
        Log::info('payment_proof.submitted', [
            'proof_id' => $proof->id,
            'tenant_id' => $proof->tenant_id,
            'landlord_id' => $proof->tenant->user_id,
            'payment_id' => $proof->payment_history_id,
        ]);
    }

    public function tenantNotifiedOfReview(PaymentProof $proof): void
    {
        Log::info('payment_proof.reviewed', [
            'proof_id' => $proof->id,
            'status' => $proof->status->value,
            'tenant_id' => $proof->tenant_id,
        ]);
    }
}
