<?php

namespace Tests\Feature\Stripe;

use App\Jobs\Webhooks\ProcessStripeWebhookJob;
use App\Models\PaymentHistory;
use App\Models\StripeWebhookEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessStripeWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_rent_checkout_webhook_is_processed_once(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->for($tenant)->overdue()->create([
            'stripe_checkout_session_id' => 'cs_test_webhook',
        ]);

        $payload = [
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_webhook',
                    'mode' => 'payment',
                    'payment_intent' => 'pi_test_webhook',
                    'metadata' => [
                        'type' => 'rent_payment',
                        'payment_history_id' => (string) $payment->id,
                        'tenant_id' => (string) $tenant->id,
                        'landlord_user_id' => (string) $landlord->id,
                    ],
                ],
            ],
        ];

        $job = new ProcessStripeWebhookJob('evt_test_1', 'checkout.session.completed', $payload);
        $job->handle(app(\App\Services\Webhooks\StripeWebhookDispatcher::class));
        $job->handle(app(\App\Services\Webhooks\StripeWebhookDispatcher::class));

        $this->assertNotNull(StripeWebhookEvent::query()->where('stripe_event_id', 'evt_test_1')->value('processed_at'));
        $payment->refresh();
        $this->assertTrue($payment->status->isSettled());

        $this->assertSame(1, StripeWebhookEvent::query()->where('stripe_event_id', 'evt_test_1')->count());
    }
}
