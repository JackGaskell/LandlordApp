<?php

namespace App\DataTransferObjects\Portal;

readonly class TenantPaymentStreak
{
    public function __construct(
        public int $currentMonths,
        public int $bestMonths,
        public string $message,
    ) {}
}
