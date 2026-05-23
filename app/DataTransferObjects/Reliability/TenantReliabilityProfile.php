<?php

namespace App\DataTransferObjects\Reliability;

use App\Enums\ReliabilityBadge;
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

    public function portalHeadline(): string
    {
        return match ($this->badge) {
            ReliabilityBadge::New => 'Your journey starts here',
            ReliabilityBadge::Bronze => 'Momentum is building',
            ReliabilityBadge::Silver => 'Solid consistency',
            ReliabilityBadge::Gold => 'Excellent track record',
            ReliabilityBadge::Platinum => 'Elite reliability',
        };
    }

    public function portalMessage(): string
    {
        if ($this->trackedPeriods === 0) {
            return 'Your reliability score grows with each rent period you complete on time.';
        }

        return match ($this->badge) {
            ReliabilityBadge::Platinum => 'You are among the most consistent tenants — keep the streak alive.',
            ReliabilityBadge::Gold => 'On-time payments are becoming a habit. One more month strengthens your score.',
            ReliabilityBadge::Silver => 'You are paying reliably. Each on-time month moves you closer to Gold.',
            ReliabilityBadge::Bronze => 'Every on-time payment counts. Your next rent is a chance to level up.',
            ReliabilityBadge::New => 'Complete your next payment on time to start building your score.',
        };
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
