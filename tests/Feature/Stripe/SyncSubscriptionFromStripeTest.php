<?php

namespace Tests\Feature\Stripe;

use App\Actions\Billing\SyncSubscriptionFromStripeAction;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncSubscriptionFromStripeTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_session_syncs_landlord_subscription_fields(): void
    {
        $landlord = User::factory()->create();

        app(SyncSubscriptionFromStripeAction::class)->fromCheckoutSession([
            'id' => 'cs_sub_1',
            'customer' => 'cus_test',
            'subscription' => 'sub_test',
            'metadata' => [
                'type' => 'subscription',
                'user_id' => (string) $landlord->id,
            ],
        ]);

        $landlord->refresh();

        $this->assertSame('cus_test', $landlord->stripe_customer_id);
        $this->assertSame('sub_test', $landlord->stripe_subscription_id);
        $this->assertSame(SubscriptionStatus::Active, $landlord->subscription_status);
        $this->assertTrue($landlord->hasActiveSubscription());
    }
}
