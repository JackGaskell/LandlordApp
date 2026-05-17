<?php

namespace App\Services\Webhooks\Handlers;

use App\Actions\Payments\ConfirmRentPaymentFromStripeAction;
use App\Contracts\Webhooks\StripeWebhookHandler;
use App\Services\Webhooks\Concerns\InteractsWithStripePayload;

/**
 * Tenant completed Stripe Checkout for a rent period (mode=payment).
 */
class RentCheckoutCompletedHandler implements StripeWebhookHandler
{
    use InteractsWithStripePayload;

    public function __construct(
        protected ConfirmRentPaymentFromStripeAction $confirmRentPayment,
    ) {}

    public function handles(string $eventType): bool
    {
        return $eventType === 'checkout.session.completed';
    }

    public function handle(array $payload): void
    {
        $session = $this->eventObject($payload);

        if (($session['mode'] ?? null) !== 'payment') {
            return;
        }

        $metadata = $this->metadata($session);

        if (($metadata['type'] ?? null) !== 'rent_payment') {
            return;
        }

        $this->confirmRentPayment->execute(
            checkoutSessionId: (string) $session['id'],
            paymentIntentId: isset($session['payment_intent']) ? (string) $session['payment_intent'] : null,
            paymentHistoryId: isset($metadata['payment_history_id']) ? (int) $metadata['payment_history_id'] : null,
        );
    }
}
