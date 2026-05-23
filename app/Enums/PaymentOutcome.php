<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

/**
 * Behavioural outcome for reliability scoring (derived from status + dates).
 */
enum PaymentOutcome: string
{
    use InteractsWithPresentation;

    case OnTime = 'on_time';
    case Late = 'late';
    case Missed = 'missed';
    case Pending = 'pending';
    case Partial = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::OnTime => 'On time',
            self::Late => 'Late',
            self::Missed => 'Missed',
            self::Pending => 'Upcoming',
            self::Partial => 'Partial',
        };
    }

    public function countsTowardStreak(): bool
    {
        return $this === self::OnTime;
    }

    public function isNegative(): bool
    {
        return in_array($this, [self::Late, self::Missed, self::Partial], true);
    }

    public function timelineTone(): string
    {
        return match ($this) {
            self::OnTime => 'emerald',
            self::Late => 'amber',
            self::Missed => 'slate',
            self::Pending => 'brand',
            self::Partial => 'orange',
        };
    }
}
