<?php

namespace App\DataTransferObjects\Reminders;

use App\Enums\ReminderType;

readonly class ReminderScheduleEntry
{
    public function __construct(
        public ReminderType $type,
        public int $days,
        public int $signedOffset,
    ) {}
}
