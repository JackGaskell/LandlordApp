<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

enum PaymentMethod: string
{
    use InteractsWithPresentation;

    case BankTransfer = 'bank_transfer';
    case DebitCard = 'debit_card';
    case Cash = 'cash';
    case Stripe = 'stripe';
    case StandingOrder = 'standing_order';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank transfer',
            self::DebitCard => 'Debit card',
            self::Cash => 'Cash',
            self::Stripe => 'Card (Stripe)',
            self::StandingOrder => 'Standing order',
            self::Other => 'Other',
        };
    }

    public static function fromRecordedVia(PaymentRecordedVia $via): self
    {
        return match ($via) {
            PaymentRecordedVia::Stripe => self::Stripe,
            PaymentRecordedVia::Manual => self::BankTransfer,
        };
    }
}
