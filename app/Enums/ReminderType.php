<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

/**
 * Classifies automated rent reminders (used by queued reminder jobs).
 */
enum ReminderType: string
{
    use InteractsWithPresentation;

    case BeforeDue = 'before_due';
    case AfterDue = 'after_due';

    public function label(): string
    {
        return match ($this) {
            self::BeforeDue => 'Before due date',
            self::AfterDue => 'After due date',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BeforeDue => 'bg-sky-100 text-sky-800',
            self::AfterDue => 'bg-violet-100 text-violet-800',
        };
    }

    /**
     * Day offset sign: negative = before due date, positive = after.
     */
    public function signedOffset(int $days): int
    {
        return $this === self::BeforeDue ? -abs($days) : abs($days);
    }
}
