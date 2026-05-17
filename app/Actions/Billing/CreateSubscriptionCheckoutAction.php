<?php

namespace App\Actions\Billing;

use App\Contracts\Billing\SubscriptionBillingGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Models\User;

/**
 * Starts Stripe Checkout for landlord SaaS subscription.
 */
class CreateSubscriptionCheckoutAction
{
    public function __construct(
        protected SubscriptionBillingGateway $billing,
    ) {}

    public function execute(User $landlord): CheckoutSessionResult
    {
        return $this->billing->createSubscriptionCheckout($landlord);
    }
}
