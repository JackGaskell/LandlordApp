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

    /**
     * Encouraging copy for the tenant portal (non-punitive).
     */
    public function portalHeadline(): string
    {
        return match (true) {
            $this->paymentsTracked === 0 => 'Getting started',
            $this->score >= 90 => 'Excellent consistency',
            $this->score >= 75 => 'Strong track record',
            $this->score >= 50 => 'Room to grow',
            default => 'Fresh start available',
        };
    }

    public function portalMessage(): string
    {
        return match (true) {
            $this->paymentsTracked === 0 => 'Your reliability score will build as rent periods are recorded.',
            $this->score >= 90 => 'You are paying on time consistently — keep the momentum going.',
            $this->score >= 75 => 'Most of your payments are on time. Small improvements add up quickly.',
            $this->score >= 50 => 'Each on-time payment strengthens your score and your streak.',
            default => 'Your next on-time payment is a clean step forward.',
        };
    }
}
