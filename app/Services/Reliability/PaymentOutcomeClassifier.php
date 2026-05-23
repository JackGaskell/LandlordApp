<?php

namespace App\Services\Reliability;

use App\Enums\PaymentOutcome;
use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;

class PaymentOutcomeClassifier
{
    public function __construct(
        protected LatePaymentDetector $latePayments,
    ) {}

    public function classify(PaymentHistory $payment): PaymentOutcome
    {
        if ($payment->status === PaymentStatus::PartiallyPaid) {
            return PaymentOutcome::Partial;
        }

        if ($payment->status === PaymentStatus::Paid && $payment->paid_at) {
            return $this->latePayments->isLate($payment)
                ? PaymentOutcome::Late
                : PaymentOutcome::OnTime;
        }

        if ($payment->status === PaymentStatus::Overdue) {
            return PaymentOutcome::Missed;
        }

        if ($payment->status === PaymentStatus::DueSoon) {
            if ($this->latePayments->isPastDueUnpaid($payment)) {
                return PaymentOutcome::Missed;
            }

            return PaymentOutcome::Pending;
        }

        return PaymentOutcome::Pending;
    }
}
