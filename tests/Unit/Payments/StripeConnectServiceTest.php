<?php

namespace Tests\Unit\Payments;

use App\Models\User;
use App\Services\Payments\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_accept_rent_when_connect_account_is_ready(): void
    {
        config([
            'landlord.stripe.enabled' => true,
            'landlord.stripe.connect.enabled' => true,
            'landlord.stripe.connect.required' => true,
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        $landlord = User::factory()->create([
            'stripe_connect_account_id' => 'acct_ready',
            'stripe_connect_charges_enabled' => true,
        ]);

        $this->assertTrue(app(StripeConnectService::class)->canAcceptRentPayments($landlord));
    }

    public function test_landlord_cannot_accept_rent_without_charges_enabled(): void
    {
        config([
            'landlord.stripe.enabled' => true,
            'landlord.stripe.connect.enabled' => true,
            'landlord.stripe.connect.required' => true,
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        $landlord = User::factory()->create([
            'stripe_connect_account_id' => 'acct_pending',
            'stripe_connect_charges_enabled' => false,
        ]);

        $this->assertFalse(app(StripeConnectService::class)->canAcceptRentPayments($landlord));
    }
}
