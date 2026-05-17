<?php

namespace App\Services\Webhooks\Handlers;

use App\Actions\Billing\SyncSubscriptionFromStripeAction;
use App\Contracts\Webhooks\StripeWebhookHandler;
use App\Services\Webhooks\Concerns\InteractsWithStripePayload;

/**
 * Landlord completed Stripe Checkout for SaaS subscription (mode=subscription).
 */
class SubscriptionCheckoutCompletedHandler implements StripeWebhookHandler
{
    use InteractsWithStripePayload;

    public function __construct(
        protected SyncSubscriptionFromStripeAction $syncSubscription,
    ) {}

    public function handles(string $eventType): bool
    {
        return $eventType === 'checkout.session.completed';
    }

    public function handle(array $payload): void
    {
        $session = $this->eventObject($payload);

        if (($session['mode'] ?? null) !== 'subscription') {
            return;
        }

        $metadata = $this->metadata($session);

        if (($metadata['type'] ?? null) !== 'subscription') {
            return;
        }

        $this->syncSubscription->fromCheckoutSession($session);
    }
}
