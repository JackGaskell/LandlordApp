<?php

namespace Tests\Feature\Settings;

use App\Models\LandlordSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_update_reminder_schedule_with_day_picker_values(): void
    {
        $user = User::factory()->create();
        $setting = LandlordSetting::factory()->for($user)->create([
            'reminder_days_before' => [7, 3, 1],
            'overdue_reminder_days' => [1, 3, 7],
            'email_reminders_enabled' => true,
        ]);

        $response = $this->actingAs($user)->put(route('settings.update', $setting), [
            'reminder_days_before' => ['7', '1'],
            'overdue_reminder_days' => ['3', '7'],
            'email_reminders_enabled' => '1',
        ]);

        $response
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status');

        $setting->refresh();

        $this->assertSame([7, 1], $setting->reminder_days_before);
        $this->assertSame([3, 7], $setting->overdue_reminder_days);
        $this->assertTrue($setting->email_reminders_enabled);
    }

    public function test_reminder_settings_page_renders_day_picker(): void
    {
        $user = User::factory()->create();
        LandlordSetting::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Before rent is due', false)
            ->assertSee('If rent is late', false)
            ->assertSee('Email reminders', false)
            ->assertSee('Due day', false);
    }

    public function test_landlord_can_save_due_day_in_before_due_schedule(): void
    {
        $user = User::factory()->create();
        $setting = LandlordSetting::factory()->for($user)->create([
            'reminder_days_before' => [7, 3, 1],
            'overdue_reminder_days' => [1, 3, 7],
        ]);

        $this->actingAs($user)->put(route('settings.update', $setting), [
            'reminder_days_before' => ['0', '3'],
            'overdue_reminder_days' => ['1'],
            'email_reminders_enabled' => '1',
        ])->assertRedirect(route('settings.edit'));

        $setting->refresh();

        $this->assertSame([0, 3], $setting->reminder_days_before);
    }
}
