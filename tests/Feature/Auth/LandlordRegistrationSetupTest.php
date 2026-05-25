<?php

namespace Tests\Feature\Auth;

use App\Models\LandlordSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordRegistrationSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['landlord.auth.registration_enabled' => true]);
    }

    public function test_new_landlord_gets_default_reminder_settings(): void
    {
        $this->post('/register', [
            'first_name' => 'Sam',
            'last_name' => 'Patel',
            'email' => 'sam@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $landlord = User::query()->where('email', 'sam@example.com')->first();

        $settings = LandlordSetting::query()->where('user_id', $landlord->id)->first();

        $this->assertNotNull($settings);
        $this->assertTrue($settings->email_reminders_enabled);
        $this->assertSame([3, 1, 0], $settings->reminder_days_before);
    }
}
