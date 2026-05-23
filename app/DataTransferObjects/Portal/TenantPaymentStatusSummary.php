<?php

namespace App\DataTransferObjects\Portal;

use App\Enums\PaymentStatus;

readonly class TenantPaymentStatusSummary
{
    public function __construct(
        public PaymentStatus $status,
        public string $headline,
        public string $message,
        public bool $canUploadProof,
        public bool $canPayOnline,
    ) {}

    public function portalPrimaryAction(): ?string
    {
        if ($this->canPayOnline) {
            return 'pay';
        }

        if ($this->canUploadProof) {
            return 'upload';
        }

        return null;
    }

    public function portalPrimaryActionLabel(): string
    {
        return match ($this->portalPrimaryAction()) {
            'pay' => 'Pay now',
            'upload' => 'Upload proof of payment',
            default => '',
        };
    }

    public function portalIsActionable(): bool
    {
        return $this->portalPrimaryAction() !== null;
    }

    public function portalIsPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function portalStatusLabel(): string
    {
        return match ($this->status) {
            PaymentStatus::Paid => 'Paid',
            PaymentStatus::DueSoon => 'Due soon',
            PaymentStatus::Overdue => 'Overdue',
            PaymentStatus::PartiallyPaid => 'Partially paid',
        };
    }

    public function portalStatusTone(): string
    {
        return match ($this->status) {
            PaymentStatus::Paid => 'success',
            PaymentStatus::DueSoon => 'brand',
            PaymentStatus::Overdue => 'warning',
            PaymentStatus::PartiallyPaid => 'info',
        };
    }

    public function portalNextStep(): string
    {
        return match ($this->status) {
            PaymentStatus::Paid => 'You\'re all set for this period.',
            PaymentStatus::DueSoon => 'Pay on or before the due date to protect your score.',
            PaymentStatus::Overdue => 'Pay as soon as you can, then upload proof to update your record.',
            PaymentStatus::PartiallyPaid => 'Complete the remaining amount or upload proof when ready.',
        };
    }
}
