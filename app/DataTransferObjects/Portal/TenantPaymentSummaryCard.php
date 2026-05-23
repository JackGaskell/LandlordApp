<?php

namespace App\DataTransferObjects\Portal;

readonly class TenantPaymentSummaryCard
{
    public function __construct(
        public string $label,
        public string $value,
        public ?string $hint = null,
        public string $tone = 'neutral',
    ) {}
}
