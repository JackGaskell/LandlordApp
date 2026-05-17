<?php

namespace App\Services\Billing;

use App\Contracts\Billing\SubscriptionBillingGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\User;

/**
 * Stripe implementation — wire up when stripe/stripe-php is installed.
 *
 * createSubscriptionCheckout() should:
 * - Ensure stripe_customer_id on the user (create Customer if missing)
 * - Create Checkout Session (mode=subscription, line_items=[price_id])
 * - metadata: { type: "subscription", user_id }
 * - success_url / cancel_url from config('landlord.stripe.checkout_urls')
 */
class StripeSubscriptionBillingGateway implements SubscriptionBillingGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'))
            && filled(config('landlord.stripe.subscription_price_id'))
            && class_exists(\Stripe\StripeClient::class);
    }

    public function createSubscriptionCheckout(User $landlord): CheckoutSessionResult
    {
        if (! $this->isConfigured()) {
            throw StripeNotConfiguredException::forFeature('subscription checkout');
        }

        // TODO: \Stripe\Checkout\Session::create([...])
        throw StripeNotConfiguredException::forFeature('subscription checkout (not implemented)');
    }

    public function createCustomerPortalUrl(User $landlord): string
    {
        if (! $this->isConfigured()) {
            throw StripeNotConfiguredException::forFeature('customer portal');
        }

        // TODO: \Stripe\BillingPortal\Session::create([...])
        throw StripeNotConfiguredException::forFeature('customer portal (not implemented)');
    }
}
