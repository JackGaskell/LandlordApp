<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Tenants\TenantReliabilityScore;
use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Scores tenant payment behaviour from historical rent records.
 */
class TenantReliabilityService
{
    /**
     * @return array{on_time: float, late: float, missed: float}
     */
    protected function weights(): array
    {
        return config('landlord.reliability');
    }

    public function score(Tenant $tenant): TenantReliabilityScore
    {
        $payments = $tenant->paymentHistories()
            ->orderBy('due_date')
            ->get();

        return $this->scoreFromPayments($tenant, $payments);
    }

    /**
     * @return Collection<int, TenantReliabilityScore>
     */
    public function scoresForLandlord(User $landlord): Collection
    {
        return Tenant::query()
            ->forLandlord($landlord)
            ->with('paymentHistories')
            ->orderBy('name')
            ->get()
            ->map(fn (Tenant $tenant) => $this->scoreFromPayments($tenant, $tenant->paymentHistories));
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     */
    protected function scoreFromPayments(Tenant $tenant, Collection $payments): TenantReliabilityScore
    {
        $tracked = $payments->count();

        if ($tracked === 0) {
            return new TenantReliabilityScore(
                tenantId: $tenant->id,
                tenantName: $tenant->name,
                score: 100.0,
                grade: 'New',
                paymentsOnTime: 0,
                paymentsLate: 0,
                paymentsMissed: 0,
                paymentsTracked: 0,
            );
        }

        $onTime = 0;
        $late = 0;
        $missed = 0;

        $weights = $this->weights();

        foreach ($payments as $payment) {
            if ($payment->status === PaymentStatus::Paid) {
                if ($payment->paid_at && $payment->paid_at->lte($payment->due_date->endOfDay())) {
                    $onTime++;
                } else {
                    $late++;
                }

                continue;
            }

            if ($payment->status === PaymentStatus::PartiallyPaid) {
                $late++;

                continue;
            }

            if ($payment->status === PaymentStatus::Overdue) {
                $missed++;

                continue;
            }

            if ($payment->status === PaymentStatus::DueSoon && $payment->due_date->isPast()) {
                $missed++;
            }
        }

        $weightedSum = ($onTime * $weights['on_time_weight'])
            + ($late * $weights['late_weight'])
            + ($missed * $weights['missed_weight']);

        $maxPossible = $tracked * $weights['on_time_weight'];
        $normalised = $maxPossible > 0
            ? max(0, min(100, ($weightedSum / $maxPossible) * 100))
            : 100.0;

        return new TenantReliabilityScore(
            tenantId: $tenant->id,
            tenantName: $tenant->name,
            score: round($normalised, 1),
            grade: $this->gradeForScore($normalised),
            paymentsOnTime: $onTime,
            paymentsLate: $late,
            paymentsMissed: $missed,
            paymentsTracked: $tracked,
        );
    }

    protected function gradeForScore(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 50 => 'Fair',
            default => 'At risk',
        };
    }
}
