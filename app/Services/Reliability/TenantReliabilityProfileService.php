<?php

namespace App\Services\Reliability;

use App\DataTransferObjects\Reliability\PaymentTimelineEntry;
use App\DataTransferObjects\Reliability\TenantReliabilityProfile;
use App\Enums\PaymentMethod;
use App\Enums\PaymentOutcome;
use App\Enums\ReliabilityBadge;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

class TenantReliabilityProfileService
{
    public function __construct(
        protected ReliabilityScoreCalculator $scores,
        protected PaymentStreakCalculator $streaks,
        protected PaymentConsistencyCalculator $consistency,
        protected PaymentOutcomeClassifier $classifier,
    ) {}

    public function profile(Tenant $tenant, bool $preferCache = true): TenantReliabilityProfile
    {
        if ($preferCache && $this->cacheIsFresh($tenant)) {
            return $this->profileFromCache($tenant);
        }

        return $this->profileFromPayments($tenant);
    }

    /**
     * @return Collection<int, TenantReliabilityProfile>
     */
    public function profilesForLandlord(User $landlord): Collection
    {
        return Tenant::query()
            ->forLandlord($landlord)
            ->orderBy('name')
            ->get()
            ->map(fn (Tenant $tenant) => $this->profile($tenant));
    }

    public function profileFromPayments(Tenant $tenant): TenantReliabilityProfile
    {
        $payments = $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->get();

        return $this->buildProfile($tenant, $payments);
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     */
    public function buildProfile(Tenant $tenant, Collection $payments): TenantReliabilityProfile
    {
        $metrics = $this->scores->calculate($payments);
        $streak = $this->streaks->calculate($payments);
        $windowMonths = (int) config('landlord.reliability.consistency_window_months', 12);
        $consistencyRate = $this->consistency->rollingOnTimeRate($payments, $windowMonths);

        $badge = ReliabilityBadge::forScore($metrics['score'], $metrics['tracked']);

        return new TenantReliabilityProfile(
            tenantId: $tenant->id,
            tenantName: $tenant->name,
            score: $metrics['score'],
            badge: $badge,
            currentStreak: $streak['current'],
            bestStreak: max($streak['current'], $streak['best']),
            totalOnTime: $metrics['on_time'],
            lateCount: $metrics['late'],
            missedCount: $metrics['missed'],
            partialCount: $metrics['partial'],
            trackedPeriods: $metrics['tracked'],
            consistencyRate: $consistencyRate,
            consistencyWindowMonths: $windowMonths,
            timeline: $this->buildTimeline($payments),
            fromCache: false,
        );
    }

    protected function profileFromCache(Tenant $tenant): TenantReliabilityProfile
    {
        $payments = $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->limit(24)
            ->get();

        $badge = ReliabilityBadge::forScore((float) $tenant->reliability_score, (int) $tenant->reliability_tracked_periods);

        return new TenantReliabilityProfile(
            tenantId: $tenant->id,
            tenantName: $tenant->name,
            score: (float) $tenant->reliability_score,
            badge: $badge,
            currentStreak: (int) $tenant->reliability_current_streak,
            bestStreak: (int) $tenant->reliability_best_streak,
            totalOnTime: (int) $tenant->reliability_on_time_count,
            lateCount: (int) $tenant->reliability_late_count,
            missedCount: (int) $tenant->reliability_missed_count,
            partialCount: 0,
            trackedPeriods: (int) $tenant->reliability_tracked_periods,
            consistencyRate: (float) ($tenant->reliability_consistency_rate ?? 100),
            consistencyWindowMonths: (int) config('landlord.reliability.consistency_window_months', 12),
            timeline: $this->buildTimeline($payments),
            fromCache: true,
        );
    }

    protected function cacheIsFresh(Tenant $tenant): bool
    {
        return $tenant->reliability_calculated_at !== null
            && $tenant->reliability_calculated_at->gte(
                now()->subMinutes((int) config('landlord.reliability.cache_ttl_minutes', 30)),
            );
    }

    /**
     * @param  Collection<int, PaymentHistory>  $payments
     * @return Collection<int, PaymentTimelineEntry>
     */
    public function buildTimeline(Collection $payments, int $limit = 12): Collection
    {
        return $payments
            ->sortByDesc('due_date')
            ->take($limit)
            ->map(function (PaymentHistory $payment) {
                $outcome = $payment->payment_outcome ?? $this->classifier->classify($payment);

                return new PaymentTimelineEntry(
                    id: $payment->id,
                    periodLabel: $payment->due_date->format('F Y'),
                    amount: (float) $payment->amount,
                    dueDate: $payment->due_date,
                    paidAt: $payment->paid_at,
                    status: $payment->status,
                    outcome: $outcome,
                    daysLate: $payment->days_late,
                    paymentMethodLabel: $payment->payment_method?->label(),
                );
            })
            ->values();
    }

    public function persistCache(Tenant $tenant, TenantReliabilityProfile $profile): void
    {
        $tenant->forceFill([
            'reliability_score' => $profile->score,
            'reliability_current_streak' => $profile->currentStreak,
            'reliability_best_streak' => $profile->bestStreak,
            'reliability_on_time_count' => $profile->totalOnTime,
            'reliability_late_count' => $profile->lateCount,
            'reliability_missed_count' => $profile->missedCount,
            'reliability_consistency_rate' => $profile->consistencyRate,
            'reliability_tracked_periods' => $profile->trackedPeriods,
            'reliability_calculated_at' => now(),
        ])->save();
    }
}
