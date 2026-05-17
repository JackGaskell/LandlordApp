<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;
use Carbon\CarbonInterface;

/**
 * Centralises how rent payment statuses are derived and kept in sync.
 */
class PaymentStatusService
{
    public function __construct(
        protected int $dueSoonDays = 7,
    ) {
        $this->dueSoonDays = (int) config('landlord.collection.due_soon_days', $dueSoonDays);
    }

    public function dueSoonDays(): int
    {
        return $this->dueSoonDays;
    }

    /**
     * Determine the correct status from dates and amounts.
     */
    public function resolve(
        CarbonInterface $dueDate,
        ?CarbonInterface $paidAt = null,
        ?float $amount = null,
        ?float $expectedAmount = null,
    ): PaymentStatus {
        $amountDue = $expectedAmount ?? $amount ?? 0;

        if ($paidAt !== null) {
            if ($amount !== null && $amountDue > 0 && $amount < $amountDue) {
                return PaymentStatus::PartiallyPaid;
            }

            return PaymentStatus::Paid;
        }

        if ($dueDate->isPast()) {
            return PaymentStatus::Overdue;
        }

        if ($dueDate->lte(now()->addDays($this->dueSoonDays))) {
            return PaymentStatus::DueSoon;
        }

        return PaymentStatus::DueSoon;
    }

    /**
     * Recompute and persist status from the payment's current fields.
     */
    public function sync(PaymentHistory $payment): PaymentHistory
    {
        $payment->update([
            'status' => $this->resolve(
                $payment->due_date,
                $payment->paid_at,
                (float) $payment->amount,
                (float) $payment->amount,
            ),
        ]);

        return $payment->fresh();
    }

    public function isOutstanding(PaymentStatus $status): bool
    {
        return $status->isOutstanding();
    }
}
