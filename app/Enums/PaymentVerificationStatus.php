<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

/**
 * Stored on payment_histories.verification_status — landlord confirmation of payment.
 */
enum PaymentVerificationStatus: string
{
    use InteractsWithPresentation;

    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::Pending => 'Pending review',
            self::Verified => 'Verified',
            self::Disputed => 'Disputed',
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::Unverified => 'neutral',
            self::Pending => 'brand',
            self::Verified => 'success',
            self::Disputed => 'warning',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unverified => 'bg-white/[0.08] text-slate-300 ring-white/10',
            self::Pending => 'bg-brand-500/15 text-brand-300 ring-brand-500/25',
            self::Verified => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/25',
            self::Disputed => 'bg-amber-500/15 text-amber-300 ring-amber-500/25',
        };
    }
}
