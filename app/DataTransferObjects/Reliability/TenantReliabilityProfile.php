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

    public function portalScoreIsEstablished(): bool
    {
        return $this->trackedPeriods > 0;
    }

    public function portalScoreDisplay(): string
    {
        return $this->portalScoreIsEstablished() ? $this->scoreFormatted() : 'New';
    }

    public function portalScoreSubtitle(): string
    {
        return $this->portalScoreIsEstablished() ? 'out of 100' : 'Not started yet';
    }

    public function portalScoreAriaLabel(): string
    {
        if (! $this->portalScoreIsEstablished()) {
            return 'Tenant score not started yet. Your first scored payment begins your score out of 100.';
        }

        return 'Tenant score '.$this->scoreFormatted().' out of 100';
    }

    public function portalScoreProgressPercent(): int
    {
        if (! $this->portalScoreIsEstablished()) {
            return 0;
        }

        return min(100, max(0, (int) round($this->score)));
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
        return 'A strong score shows you pay rent consistently — it helps you build a rental record you can be proud of.';
    }

    public function portalHeadline(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your score begins with your first on-time payment';
        }

        return $this->scoreTier()->portalSummary();
    }

    public function portalMessage(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'When your next rent lands on time, you start building a profile that reflects reliability.';
        }

        if ($this->currentStreak >= 3) {
            return 'Nice streak — '.$this->scoreTier()->portalEncouragement();
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
                'label' => 'On time',
                'value' => (string) $this->totalOnTime,
                'hint' => $this->trackedPeriods > 0
                    ? 'Across '.$this->trackedPeriods.' '.str('month')->plural($this->trackedPeriods)
                    : 'Shows once you have history',
            ],
            [
                'label' => 'Consistency',
                'value' => $this->consistencyFormatted().'%',
                'hint' => 'Last '.$this->consistencyWindowMonths.' months',
            ],
            [
                'label' => 'Streak',
                'value' => (string) $this->currentStreak,
                'hint' => $this->currentStreak === 1 ? 'Month in a row' : 'Months in a row',
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
                'label' => 'First win',
                'description' => 'First on-time payment',
                'unlocked' => $this->totalOnTime >= 1,
            ],
            [
                'id' => 'streak_builder',
                'label' => 'On a roll',
                'description' => '3 months in a row',
                'unlocked' => $this->currentStreak >= 3,
            ],
            [
                'id' => 'steady_payer',
                'label' => 'Steady rhythm',
                'description' => '80%+ consistency',
                'unlocked' => $this->consistencyRate >= 80 && $this->trackedPeriods > 0,
            ],
            [
                'id' => 'trusted_profile',
                'label' => 'Trusted',
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
            'Pay on or before your due date',
            'Keep your monthly streak going',
            'Share proof after you pay so your record stays accurate',
        ];
    }

    public function portalImprovementFocus(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your first on-time payment is what kicks everything off — score, streak, and consistency.';
        }

        $next = $this->portalNextTier();

        if ($next === null) {
            return 'You are at the top tier. Keep doing what you are doing and your score stays protected.';
        }

        $points = $this->portalPointsToNextTier();

        return $points > 0
            ? "About {$points} points to {$next->label()} — your next on-time payment is the simplest way there."
            : "You are almost at {$next->label()}. One more smooth month could do it.";
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
            return 'You are on a roll — each month you pay on time adds to your story.';
        }

        if ($this->currentStreak >= 1) {
            return 'Momentum is building. Your next on-time payment keeps the streak alive.';
        }

        return 'Your next on-time payment starts a fresh streak — one month at a time.';
    }

    public function portalProgressHint(): ?string
    {
        if ($this->trackedPeriods === 0) {
            return 'First payment starts your score';
        }

        $next = $this->portalNextTier();

        if ($next === null) {
            return 'Top tier';
        }

        $points = $this->portalPointsToNextTier();

        return $points > 0
            ? "{$points} pts to {$next->label()}"
            : "Almost {$next->label()}";
    }

    public function portalCompactMessage(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your first on-time payment begins your record.';
        }

        if ($this->currentStreak >= 3) {
            return 'Strong streak — keep paying on time.';
        }

        return $this->scoreTier()->portalEncouragement();
    }

    public function portalProgressionCurrentLine(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your score hasn\'t started yet';
        }

        return 'You\'re at the '.$this->scoreTier()->scaleLabel().' stage';
    }

    public function portalProgressionNextLine(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Pay on time to begin';
        }

        $next = $this->portalNextTier();

        if ($next === null) {
            return 'Highest stage reached';
        }

        $points = $this->portalPointsToNextTier();

        return $points > 0
            ? "{$points} points to reach {$next->scaleLabel()}"
            : "Almost at {$next->scaleLabel()}";
    }

    public function portalProgressionSupportLine(): string
    {
        return 'Pay rent on time to move up.';
    }

    public function portalHasPositiveRecentOutcome(): bool
    {
        $latest = $this->timeline->first();

        return $latest !== null && $latest->outcome === PaymentOutcome::OnTime;
    }

    /**
     * @return list<array{label: string, value: string, icon: string, tone: string}>
     */
    public function portalCompactStats(): array
    {
        return [
            [
                'label' => 'On time',
                'value' => (string) $this->totalOnTime,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Late',
                'value' => (string) $this->lateCount,
                'icon' => 'clock',
                'tone' => 'warning',
            ],
            [
                'label' => 'Month streak',
                'value' => (string) $this->currentStreak,
                'icon' => 'flame',
                'tone' => $this->currentStreak > 0 ? 'streak' : 'default',
            ],
            [
                'label' => 'Consistency',
                'value' => $this->consistencyFormatted().'%',
                'icon' => 'chart',
                'tone' => 'brand',
            ],
        ];
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
