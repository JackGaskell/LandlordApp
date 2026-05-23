<?php

namespace App\DataTransferObjects\Portal;

use App\Enums\TenantCollectionStatus;
use Carbon\CarbonInterface;

readonly class TenantUpcomingRent
{
    public function __construct(
        public float $amount,
        public CarbonInterface $dueDate,
        public int $daysUntilDue,
        public ?int $daysOverdue,
        public bool $isOverdue,
        public string $dueLabel,
        public ?int $paymentId,
        public TenantCollectionStatus $collectionStatus,
    ) {}

    public function amountFormatted(): string
    {
        return '£'.number_format($this->amount, 2);
    }

    public function cardTitle(): string
    {
        return $this->isOverdue ? 'Outstanding rent' : 'Upcoming rent';
    }

    public function portalPeriodLabel(): string
    {
        return $this->dueDate->format('F Y').' rent';
    }

    public function portalDueDateLong(): string
    {
        return $this->dueDate->format('l, j M Y');
    }
}
