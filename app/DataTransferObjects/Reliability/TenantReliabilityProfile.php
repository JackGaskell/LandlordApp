<?php

namespace App\DataTransferObjects\Reliability;

use App\Enums\PaymentOutcome;
use App\Enums\ReliabilityBadge;
use App\Enums\TenantScoreTier;
use Illuminate\Support\Collection;

readonly class TenantReliabilityProfile
{
    /**
     * @param  Collection<int, PaymentTimelineEntry>  $timeline
     */
    public function __construct(
        public int $tenantId,
        public string $tenantName,
        public float $score,
        public ReliabilityBadge $badge,
        public int $currentStreak,
        public int $bestStreak,
        public int $totalOnTime,
        public int $lateCount,
        public int $missedCount,
        public int $partialCount,
        public int $trackedPeriods,
        public float $consistencyRate,
        public int $consistencyWindowMonths,
        public Collection $timeline,
        public bool $fromCache = false,
    ) {}

    public function scoreFormatted(): string
    {
        return number_format($this->score, 0);
    }

    public function consistencyFormatted(): string
    {
        return number_format($this->consistencyRate, 0);
    }

    public function scoreTier(): TenantScoreTier
    {
        return TenantScoreTier::forScore($this->score, $this->trackedPeriods);
    }

    public function portalNextTier(): ?TenantScoreTier
    {
        return $this->scoreTier()->next();
    }

    public function portalPointsToNextTier(): ?int
    {
        $next = $this->portalNextTier();

        if ($next === null) {
            return null;
        }

        return max(0, (int) ceil($next->minScore() - $this->score));
    }

    public function portalWhyItMatters(): string
    {
        return 'Your tenant score reflects how consistently you pay rent on time. A strong score helps you build a trusted rental record.';
    }

    public function portalHeadline(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your score starts with your first payment';
        }

        return $this->scoreTier()->portalSummary();
    }

    public function portalMessage(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Pay your next rent on time to begin building a record landlords can trust.';
        }

        if ($this->currentStreak >= 3) {
            return 'Keep your streak alive — '.$this->scoreTier()->portalEncouragement();
        }

        return $this->scoreTier()->portalEncouragement();
    }

    /**
     * @return list<array{label: string, value: string, hint: string}>
     */
    public function portalScoreStats(): array
    {
        return [
            [
                'label' => 'On-time payments',
                'value' => (string) $this->totalOnTime,
                'hint' => $this->trackedPeriods > 0
                    ? 'Across '.$this->trackedPeriods.' tracked '.str('period')->plural($this->trackedPeriods)
                    : 'Tracked once you have history',
            ],
            [
                'label' => 'Consistency',
                'value' => $this->consistencyFormatted().'%',
                'hint' => 'Last '.$this->consistencyWindowMonths.' months',
            ],
            [
                'label' => 'Current streak',
                'value' => (string) $this->currentStreak,
                'hint' => $this->currentStreak === 1 ? 'Month on time' : 'Months on time',
            ],
        ];
    }

    /**
     * @return list<array{id: string, label: string, description: string, unlocked: bool}>
     */
    public function portalAchievements(): array
    {
        return [
            [
                'id' => 'first_on_time',
                'label' => 'First on time',
                'description' => 'Paid rent on time',
                'unlocked' => $this->totalOnTime >= 1,
            ],
            [
                'id' => 'streak_builder',
                'label' => 'Streak builder',
                'description' => '3+ months on time',
                'unlocked' => $this->currentStreak >= 3,
            ],
            [
                'id' => 'steady_payer',
                'label' => 'Steady payer',
                'description' => '80%+ consistency',
                'unlocked' => $this->consistencyRate >= 80 && $this->trackedPeriods > 0,
            ],
            [
                'id' => 'trusted_profile',
                'label' => 'Trusted profile',
                'description' => 'Reached Trusted tier',
                'unlocked' => in_array($this->scoreTier(), [TenantScoreTier::Trusted, TenantScoreTier::Excellent], true),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function portalMaintainActions(): array
    {
        return [
            'Pay rent on or before the due date',
            'Keep your on-time streak going each month',
            'Upload payment proof promptly after you pay',
        ];
    }

    public function portalImprovementFocus(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your first on-time payment establishes your score and begins your rental record.';
        }

        $next = $this->portalNextTier();

        if ($next === null) {
            return 'You are at the highest tier. Maintain on-time payments to protect your score.';
        }

        $points = $this->portalPointsToNextTier();

        return $points > 0
            ? "About {$points} points to reach {$next->label()} — your next on-time payment is the most direct step."
            : "You are close to {$next->label()}. One more on-time payment could move you up.";
    }

    /**
     * @return Collection<int, PaymentTimelineEntry>
     */
    public function portalRecentTimeline(): Collection
    {
        return $this->timeline->take(6)->values();
    }

    public function portalStreakMessage(): string
    {
        if ($this->currentStreak >= 3) {
            return 'Keep your streak alive — every on-time month strengthens your record.';
        }

        if ($this->currentStreak >= 1) {
            return 'You are building momentum. Your next on-time payment extends this streak.';
        }

        return 'Your next on-time payment starts a fresh streak.';
    }

    public function portalHasPositiveRecentOutcome(): bool
    {
        $latest = $this->timeline->first();

        return $latest !== null && $latest->outcome === PaymentOutcome::OnTime;
    }

    /**
     * @deprecated Use TenantReliabilityProfile directly. Kept for backward compatibility.
     */
    public function toLegacyScore(): \App\DataTransferObjects\Tenants\TenantReliabilityScore
    {
        return new \App\DataTransferObjects\Tenants\TenantReliabilityScore(
            tenantId: $this->tenantId,
            tenantName: $this->tenantName,
            score: $this->score,
            grade: $this->badge->label(),
            paymentsOnTime: $this->totalOnTime,
            paymentsLate: $this->lateCount,
            paymentsMissed: $this->missedCount,
            paymentsTracked: $this->trackedPeriods,
        );
    }
}
