<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

/**
 * Stored on payment_histories.status — rent collection state for a payment period.
 */
enum PaymentStatus: string
{
    use InteractsWithPresentation;

    case Paid = 'paid';
    case DueSoon = 'due_soon';
    case Overdue = 'overdue';
    case PartiallyPaid = 'partially_paid';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::DueSoon => 'Due soon',
            self::Overdue => 'Overdue',
            self::PartiallyPaid => 'Partially paid',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Paid => 'bg-emerald-100 text-emerald-800',
            self::DueSoon => 'bg-amber-100 text-amber-800',
            self::Overdue => 'bg-rose-100 text-rose-800',
            self::PartiallyPaid => 'bg-orange-100 text-orange-800',
        };
    }

    public function isSettled(): bool
    {
        return $this === self::Paid;
    }

    public function isOutstanding(): bool
    {
        return in_array($this, [self::DueSoon, self::Overdue, self::PartiallyPaid], true);
    }
}
