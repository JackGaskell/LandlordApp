<?php

namespace App\Services\Reliability;

use App\Enums\PaymentOutcome;
use App\Models\PaymentHistory;
use Illuminate\Support\Collection;

class PaymentConsistencyCalculator
{
    public function __construct(
        protected PaymentOutcomeClassifier $classifier,
    ) {}

    /**
     * Rolling on-time rate for closed periods within the window (percentage 0–100).
     *
     * @param  Collection<int, PaymentHistory>  $payments
     */
    public function rollingOnTimeRate(Collection $payments, ?int $months = null): float
    {
        $months = $months ?? (int) config('landlord.reliability.consistency_window_months', 12);
        $cutoff = now()->subMonths($months)->startOfDay();

        $closed = $payments->filter(function (PaymentHistory $payment) use ($cutoff) {
            if ($payment->due_date->lt($cutoff)) {
                return false;
            }

            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            return $outcome !== PaymentOutcome::Pending;
        });

        if ($closed->isEmpty()) {
            return 100.0;
        }

        $onTime = $closed->filter(function (PaymentHistory $payment) {
            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            return $outcome === PaymentOutcome::OnTime;
        })->count();

        return round(($onTime / $closed->count()) * 100, 1);
    }
}
