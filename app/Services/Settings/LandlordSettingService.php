<?php

namespace App\Services\Settings;

use App\Models\LandlordSetting;
use App\Models\User;

class LandlordSettingService
{
    public function forLandlord(User $landlord): LandlordSetting
    {
        return $landlord->landlordSetting()->firstOrCreate(
            ['user_id' => $landlord->id],
            [
                'reminder_days_before' => config('landlord.reminders.days_before_due', [7, 3, 1]),
                'overdue_reminder_days' => config('landlord.reminders.days_after_due', [1, 3, 7]),
                'email_reminders_enabled' => true,
            ],
        );
    }

    public function update(LandlordSetting $settings, array $data): LandlordSetting
    {
        $settings->update($data);

        return $settings->fresh();
    }
}
