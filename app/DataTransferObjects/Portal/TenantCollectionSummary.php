<?php

namespace App\DataTransferObjects\Portal;

use App\Enums\PaymentStatus;
use App\Enums\TenantCollectionStatus;
use Carbon\CarbonInterface;

readonly class TenantCollectionSummary
{
    public function __construct(
        public TenantCollectionStatus $status,
        public string $headline,
        public string $message,
        public string $tone,
        public ?PaymentStatus $paymentStatus,
        public ?float $amount,
        public ?CarbonInterface $dueDate,
        public ?int $daysUntilDue,
        public ?int $daysOverdue,
        public bool $isOverdue,
        public ?int $paymentId,
    ) {}

    public function amountFormatted(): ?string
    {
        return $this->amount !== null ? '£'.number_format($this->amount, 2) : null;
    }

    public function dueDateFormatted(): ?string
    {
        return $this->dueDate?->format('j M Y');
    }
}
