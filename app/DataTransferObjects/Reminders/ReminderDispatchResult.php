<?php

namespace App\DataTransferObjects\Reminders;

readonly class ReminderDispatchResult
{
    public function __construct(
        public int $queued,
        public int $skippedDuplicate,
        public int $skippedIneligible,
        public int $skippedDisabled,
    ) {}

    public function totalConsidered(): int
    {
        return $this->queued + $this->skippedDuplicate + $this->skippedIneligible + $this->skippedDisabled;
    }
}
