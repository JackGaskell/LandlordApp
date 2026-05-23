<?php

namespace App\Listeners\PaymentProofs;

use App\Contracts\Notifications\PaymentProofNotifier;
use App\Events\PaymentProofs\PaymentProofApproved;
use App\Events\PaymentProofs\PaymentProofRejected;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueTenantPaymentProofReviewNotification implements ShouldQueue
{
    public function __construct(
        protected PaymentProofNotifier $notifier,
    ) {}

    public function handle(PaymentProofApproved|PaymentProofRejected $event): void
    {
        $this->notifier->tenantNotifiedOfReview($event->proof);
    }
}
