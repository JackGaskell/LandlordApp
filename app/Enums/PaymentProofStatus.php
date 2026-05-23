<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

enum PaymentProofStatus: string
{
    use InteractsWithPresentation;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function tenantLabel(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting confirmation',
            self::Approved => 'Confirmed',
            self::Rejected => 'Not accepted',
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::Pending => 'brand',
            self::Approved => 'success',
            self::Rejected => 'warning',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-brand-500/15 text-brand-300 ring-brand-500/25',
            self::Approved => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/25',
            self::Rejected => 'bg-amber-500/15 text-amber-300 ring-amber-500/25',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected], true);
    }
}
