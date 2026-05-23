<?php

namespace App\Services\Reminders;

use App\DataTransferObjects\Reminders\ReminderScheduleEntry;
use App\Enums\ReminderType;
use App\Models\LandlordSetting;

/**
 * Builds the reminder schedule from landlord settings (or app defaults).
 */
class ReminderScheduler
{
    /**
     * @return list<ReminderScheduleEntry>
     */
    public function scheduleForSettings(LandlordSetting $settings): array
    {
        $schedule = [];

        foreach ($settings->reminder_days_before as $days) {
            $schedule[] = $this->entry(ReminderType::BeforeDue, (int) $days);
        }

        foreach ($settings->overdue_reminder_days as $days) {
            $schedule[] = $this->entry(ReminderType::AfterDue, (int) $days);
        }

        return $schedule;
    }

    /**
     * @return list<ReminderScheduleEntry>
     */
    public function defaultSchedule(): array
    {
        $schedule = [];

        foreach (config('landlord.reminders.days_before_due', []) as $days) {
            $schedule[] = $this->entry(ReminderType::BeforeDue, (int) $days);
        }

        foreach (config('landlord.reminders.days_after_due', []) as $days) {
            $schedule[] = $this->entry(ReminderType::AfterDue, (int) $days);
        }

        return $schedule;
    }

    protected function entry(ReminderType $type, int $days): ReminderScheduleEntry
    {
        return new ReminderScheduleEntry(
            type: $type,
            days: $days,
            signedOffset: $type->signedOffset($days),
        );
    }
}
