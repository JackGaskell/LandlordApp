<?php

namespace App\Observers;

use App\Models\PaymentHistory;
use App\Services\Payments\PaymentTrackingService;

class PaymentHistoryObserver
{
    public function __construct(
        protected PaymentTrackingService $tracking,
    ) {}

    public function deleted(PaymentHistory $payment): void
    {
        $this->tracking->refreshTenantReliability($payment->tenant_id);
    }
}
