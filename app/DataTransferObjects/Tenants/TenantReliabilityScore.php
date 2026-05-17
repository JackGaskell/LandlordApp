<?php

namespace App\DataTransferObjects\Tenants;

readonly class TenantReliabilityScore
{
    public function __construct(
        public int $tenantId,
        public string $tenantName,
        public float $score,
        public string $grade,
        public int $paymentsOnTime,
        public int $paymentsLate,
        public int $paymentsMissed,
        public int $paymentsTracked,
    ) {}

    public function scoreFormatted(): string
    {
        return number_format($this->score, 0);
    }
}
