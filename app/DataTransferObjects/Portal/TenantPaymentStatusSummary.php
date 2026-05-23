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
            'upload' => 'I\'ve paid — share proof',
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
        return $this->status->portalLabel();
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
            PaymentStatus::Paid => 'You are all set for this month. Nice work staying on track.',
            PaymentStatus::DueSoon => 'Paying on or before the due date keeps your score and streak moving in the right direction.',
            PaymentStatus::Overdue => 'If you have already paid, sharing proof helps keep your record accurate. Your score may be affected until payment is confirmed.',
            PaymentStatus::PartiallyPaid => 'When the rest is paid, upload proof or complete the amount — we will update your record from there.',
        };
    }

    public function portalUploadToggleHide(): string
    {
        return 'Close';
    }

    public function portalUploadToggleShow(): string
    {
        return 'Already paid? Share proof';
    }
}
