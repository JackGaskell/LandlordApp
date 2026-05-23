<?php

namespace App\Services\Reliability;

use App\Models\PaymentHistory;
use Carbon\CarbonInterface;

class LatePaymentDetector
{
    /**
     * Days after the due date that payment was received (0 = on due date, null if unpaid).
     */
    public function daysLate(PaymentHistory $payment): ?int
    {
        if (! $payment->paid_at) {
            return null;
        }

        $dueEnd = $payment->due_date->copy()->endOfDay();

        if ($payment->paid_at->lte($dueEnd)) {
            return 0;
        }

        return (int) $dueEnd->diffInDays($payment->paid_at->copy()->startOfDay());
    }

    public function isLate(PaymentHistory $payment): bool
    {
        $days = $this->daysLate($payment);

        return $days !== null && $days > 0;
    }

    public function isOnTime(PaymentHistory $payment): bool
    {
        return $payment->paid_at !== null && ! $this->isLate($payment);
    }

    public function isPastDueUnpaid(PaymentHistory $payment, ?CarbonInterface $asOf = null): bool
    {
        $asOf = $asOf ?? now();

        return $payment->paid_at === null
            && $payment->due_date->copy()->endOfDay()->lt($asOf);
    }
}
