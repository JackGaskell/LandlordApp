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
    case Verified = 'verified';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::Verified => 'Verified',
            self::Disputed => 'Disputed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unverified => 'bg-zinc-100 text-zinc-700',
            self::Verified => 'bg-emerald-100 text-emerald-800',
            self::Disputed => 'bg-rose-100 text-rose-800',
        };
    }
}
