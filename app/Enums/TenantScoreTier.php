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
            self::Improving => 'Growing',
            self::NeedsAttention => 'Finding your footing',
        };
    }

    /** Short labels for compact tier scale UI */
    public function scaleLabel(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Trusted => 'Trusted',
            self::Reliable => 'Solid',
            self::Improving => 'Growing',
            self::NeedsAttention => 'Focus',
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
            self::Excellent => 'Outstanding consistency — this is exactly the kind of record landlords value.',
            self::Trusted => 'You have earned real trust here. Keeping rent on schedule helps you stay in this tier.',
            self::Reliable => 'You pay steadily. A few more smooth months puts Trusted within reach.',
            self::Improving => 'Every on-time payment adds to your story. Small steps build a strong profile.',
            self::NeedsAttention => 'Scores shift as your record updates. Your next confirmed on-time payment is the clearest path forward.',
        };
    }

    public function portalEncouragement(): string
    {
        return match ($this) {
            self::Excellent => 'Stay in rhythm — protecting this score is about keeping the habits you already have.',
            self::Trusted => 'You are close to Excellent. Paying before each due date keeps momentum on your side.',
            self::Reliable => 'Consistency is your friend here. Prioritising the due date lifts your score naturally.',
            self::Improving => 'Pick your next due date and hit it — that single win moves the needle.',
            self::NeedsAttention => 'When you pay on time (and confirm if asked), your score reflects that progress.',
        };
    }
}
