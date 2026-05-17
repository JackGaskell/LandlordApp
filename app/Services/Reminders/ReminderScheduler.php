<?php

namespace App\Services\Reminders;

use App\Enums\ReminderType;
use App\Models\LandlordSetting;

/**
 * Coordinates when rent reminders should fire.
 */
class ReminderScheduler
{
    /**
     * @return list<array{type: ReminderType, days: int, offset: int}>
     */
    public function scheduleForSettings(LandlordSetting $settings): array
    {
        $schedule = [];

        foreach ($settings->reminder_days_before as $days) {
            $schedule[] = [
                'type' => ReminderType::BeforeDue,
                'days' => (int) $days,
                'offset' => ReminderType::BeforeDue->signedOffset((int) $days),
            ];
        }

        foreach ($settings->overdue_reminder_days as $days) {
            $schedule[] = [
                'type' => ReminderType::AfterDue,
                'days' => (int) $days,
                'offset' => ReminderType::AfterDue->signedOffset((int) $days),
            ];
        }

        return $schedule;
    }

    /**
     * @return list<int> Days relative to due date (negative = before, positive = after).
     */
    public function defaultOffsets(): array
    {
        $offsets = [];

        foreach (config('landlord.reminders.days_before_due', []) as $days) {
            $offsets[] = ReminderType::BeforeDue->signedOffset((int) $days);
        }

        foreach (config('landlord.reminders.days_after_due', []) as $days) {
            $offsets[] = ReminderType::AfterDue->signedOffset((int) $days);
        }

        return $offsets;
    }
}
