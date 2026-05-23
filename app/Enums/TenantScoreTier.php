<?php

namespace App\Enums;

enum TenantScoreTier: string
{
    case Excellent = 'excellent';
    case Trusted = 'trusted';
    case Reliable = 'reliable';
    case Improving = 'improving';
    case NeedsAttention = 'needs_attention';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Trusted => 'Trusted',
            self::Reliable => 'Reliable',
            self::Improving => 'Improving',
            self::NeedsAttention => 'Needs attention',
        };
    }

    public function minScore(): int
    {
        return match ($this) {
            self::NeedsAttention => 0,
            self::Improving => 40,
            self::Reliable => 60,
            self::Trusted => 75,
            self::Excellent => 90,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::NeedsAttention => self::Improving,
            self::Improving => self::Reliable,
            self::Reliable => self::Trusted,
            self::Trusted => self::Excellent,
            self::Excellent => null,
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::NeedsAttention,
            self::Improving,
            self::Reliable,
            self::Trusted,
            self::Excellent,
        ];
    }

    public static function forScore(float $score, int $trackedPeriods): self
    {
        if ($trackedPeriods === 0) {
            return self::Improving;
        }

        return match (true) {
            $score >= 90 => self::Excellent,
            $score >= 75 => self::Trusted,
            $score >= 60 => self::Reliable,
            $score >= 40 => self::Improving,
            default => self::NeedsAttention,
        };
    }

    public function portalTone(): string
    {
        return match ($this) {
            self::Excellent => 'excellent',
            self::Trusted => 'trusted',
            self::Reliable => 'reliable',
            self::Improving => 'improving',
            self::NeedsAttention => 'attention',
        };
    }

    public function portalSummary(): string
    {
        return match ($this) {
            self::Excellent => 'Your payment record is outstanding. Landlords see you as a highly dependable tenant.',
            self::Trusted => 'You have built strong trust. Consistent on-time payments keep you in this tier.',
            self::Reliable => 'You pay reliably. A few more strong months can move you into Trusted.',
            self::Improving => 'You are building your record. Each on-time payment strengthens your profile.',
            self::NeedsAttention => 'Your score reflects recent payment history. Your next on-time payment is the fastest way to improve.',
        };
    }

    public function portalEncouragement(): string
    {
        return match ($this) {
            self::Excellent => 'Maintain your habits — on-time payments protect the profile you have earned.',
            self::Trusted => 'You are close to Excellent. Keep your streak and pay before each due date.',
            self::Reliable => 'Steady on-time payments are the clearest path to a Trusted rating.',
            self::Improving => 'Focus on your next rent date — one on-time payment moves you forward.',
            self::NeedsAttention => 'Pay your next rent on time. Your score updates as your record improves.',
        };
    }
}
