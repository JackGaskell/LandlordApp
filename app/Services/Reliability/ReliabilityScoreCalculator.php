<?php

namespace App\Services\Reliability;

use App\Enums\PaymentOutcome;
use App\Models\PaymentHistory;
use Illuminate\Support\Collection;

class ReliabilityScoreCalculator
{
    public function __construct(
        protected PaymentOutcomeClassifier $classifier,
    ) {}

    /**
     * @return array{on_time: float, late: float, missed: float}
     */
    protected function weights(): array
    {
        return config('landlord.reliability');
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     * @return array{
     *     score: float,
     *     on_time: int,
     *     late: int,
     *     missed: int,
     *     partial: int,
     *     tracked: int
     * }
     */
    public function calculate(Collection $payments): array
    {
        $scorable = $payments->filter(function (PaymentHistory $payment) {
            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            return $outcome !== PaymentOutcome::Pending;
        });

        $tracked = $scorable->count();

        if ($tracked === 0) {
            return [
                'score' => 100.0,
                'on_time' => 0,
                'late' => 0,
                'missed' => 0,
                'partial' => 0,
                'tracked' => 0,
            ];
        }

        $onTime = 0;
        $late = 0;
        $missed = 0;
        $partial = 0;

        foreach ($scorable as $payment) {
            $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

            match ($outcome) {
                PaymentOutcome::OnTime => $onTime++,
                PaymentOutcome::Late => $late++,
                PaymentOutcome::Missed => $missed++,
                PaymentOutcome::Partial => $partial++,
                default => null,
            };
        }

        $weights = $this->weights();
        $weightedSum = ($onTime * $weights['on_time_weight'])
            + ($late * $weights['late_weight'])
            + ($missed * $weights['missed_weight'])
            + ($partial * ($weights['partial_weight'] ?? $weights['late_weight']));

        $maxPossible = $tracked * $weights['on_time_weight'];
        $score = $maxPossible > 0
            ? max(0, min(100, ($weightedSum / $maxPossible) * 100))
            : 100.0;

        return [
            'score' => round($score, 1),
            'on_time' => $onTime,
            'late' => $late,
            'missed' => $missed,
            'partial' => $partial,
            'tracked' => $tracked,
        ];
    }
}
