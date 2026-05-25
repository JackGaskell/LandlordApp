<?php

namespace Tests\Feature\Stripe;

use App\Jobs\Webhooks\ProcessStripeWebhookJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectAccountWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_updated_webhook_syncs_landlord_charges_enabled(): void
    {
        $landlord = User::factory()->create([
            'stripe_connect_account_id' => 'acct_test_sync',
            'stripe_connect_charges_enabled' => false,
        ]);

        $payload = [
            'id' => 'evt_account_1',
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_test_sync',
                    'charges_enabled' => true,
                    'details_submitted' => true,
                ],
            ],
        ];

        $job = new ProcessStripeWebhookJob('evt_account_1', 'account.updated', $payload);
        $job->handle(app(\App\Services\Webhooks\StripeWebhookDispatcher::class));

        $landlord->refresh();

        $this->assertTrue($landlord->stripe_connect_charges_enabled);
        $this->assertTrue($landlord->stripe_connect_details_submitted);
    }
}
