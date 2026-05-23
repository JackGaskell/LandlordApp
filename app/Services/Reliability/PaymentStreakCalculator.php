<?php

namespace App\Services\Reliability;

use App\Enums\PaymentOutcome;
use App\Models\PaymentHistory;
use Illuminate\Support\Collection;

class PaymentStreakCalculator
{
    public function __construct(
        protected PaymentOutcomeClassifier $classifier,
    ) {}

    /**
     * @param  Collection<int, PaymentHistory>  $payments  newest due_date first
     * @return array{current: int, best: int}
     */
    public function calculate(Collection $payments): array
    {
        $ordered = $payments->sortByDesc('due_date')->values();

        return [
            'current' => $this->trailingStreak($ordered),
            'best' => $this->bestStreak($payments),
        ];
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     */
    protected function trailingStreak(Collection $payments): int
    {
        $streak = 0;

        foreach ($payments as $payment) {
            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            if ($outcome === PaymentOutcome::Pending) {
                continue;
            }

            if ($outcome->countsTowardStreak()) {
                $streak++;

                continue;
            }

            break;
        }

        return $streak;
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     */
    protected function bestStreak(Collection $payments): int
    {
        $best = 0;
        $running = 0;

        foreach ($payments->sortBy('due_date') as $payment) {
            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            if ($outcome === PaymentOutcome::Pending) {
                continue;
            }

            if ($outcome->countsTowardStreak()) {
                $running++;
                $best = max($best, $running);

                continue;
            }

            $running = 0;
        }

        return $best;
    }
}
