<?php

namespace App\Services\Webhooks\Handlers;

use App\Actions\Billing\SyncSubscriptionFromStripeAction;
use App\Contracts\Webhooks\StripeWebhookHandler;
use App\Services\Webhooks\Concerns\InteractsWithStripePayload;

/**
 * Keeps landlord subscription_status in sync after checkout.
 */
class SubscriptionLifecycleHandler implements StripeWebhookHandler
{
    use InteractsWithStripePayload;

    /** @var list<string> */
    private const HANDLED_EVENTS = [
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.payment_failed',
    ];

    public function __construct(
        protected SyncSubscriptionFromStripeAction $syncSubscription,
    ) {}

    public function handles(string $eventType): bool
    {
        return in_array($eventType, self::HANDLED_EVENTS, true);
    }

    public function handle(array $payload): void
    {
        $object = $this->eventObject($payload);

        if (str_starts_with((string) ($payload['type'] ?? ''), 'customer.subscription.')) {
            $this->syncSubscription->fromSubscriptionObject($object);

            return;
        }

        if (($payload['type'] ?? '') === 'invoice.payment_failed') {
            $this->syncSubscription->fromInvoiceObject($object);
        }
    }
}
