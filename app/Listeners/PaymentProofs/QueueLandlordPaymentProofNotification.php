<?php

namespace App\Listeners\PaymentProofs;

use App\Contracts\Notifications\PaymentProofNotifier;
use App\Events\PaymentProofs\PaymentProofSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Notification hook — wire email/push when mail classes are ready.
 */
class QueueLandlordPaymentProofNotification implements ShouldQueue
{
    public function __construct(
        protected PaymentProofNotifier $notifier,
    ) {}

    public function handle(PaymentProofSubmitted $event): void
    {
        $this->notifier->landlordNotifiedOfSubmission($event->proof);
    }
}
