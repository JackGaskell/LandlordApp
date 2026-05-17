<?php

namespace App\Services\Billing;

use App\Contracts\Billing\SubscriptionBillingGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\User;

class NullSubscriptionBillingGateway implements SubscriptionBillingGateway
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function createSubscriptionCheckout(User $landlord): CheckoutSessionResult
    {
        throw StripeNotConfiguredException::forFeature('subscription checkout');
    }

    public function createCustomerPortalUrl(User $landlord): string
    {
        throw StripeNotConfiguredException::forFeature('customer portal');
    }
}
