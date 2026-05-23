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
}
