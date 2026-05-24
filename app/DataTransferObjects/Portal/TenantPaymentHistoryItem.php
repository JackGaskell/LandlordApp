<?php

namespace App\DataTransferObjects\Portal;

use App\Enums\PaymentStatus;
use Carbon\CarbonInterface;

readonly class TenantPaymentHistoryItem
{
    public function __construct(
        public int $id,
        public CarbonInterface $dueDate,
        public float $amount,
        public PaymentStatus $status,
        public ?CarbonInterface $paidAt,
        public string $periodLabel,
        public string $subtitle,
        public bool $isCurrentPeriod,
    ) {}

    public function amountFormatted(): string
    {
        return '£'.number_format($this->amount, 2);
    }

    public function portalStatusLabel(): string
    {
        return $this->status->portalLabel();
    }
}
