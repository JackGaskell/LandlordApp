<?php

namespace App\DataTransferObjects\Portal;

use Carbon\CarbonInterface;

readonly class TenantPortalActivityItem
{
    public function __construct(
        public string $type,
        public string $title,
        public string $description,
        public CarbonInterface $occurredAt,
    ) {}
}
