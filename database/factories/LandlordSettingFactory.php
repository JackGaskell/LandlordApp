<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LandlordSetting>
 */
class LandlordSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reminder_days_before' => [7, 3, 1],
            'overdue_reminder_days' => [1, 3, 7],
            'email_reminders_enabled' => true,
        ];
    }
}
