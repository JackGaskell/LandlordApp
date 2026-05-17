<?php

namespace App\Contracts\Billing;

use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Models\User;

/**
 * Landlord → LandlordApp SaaS billing (Stripe Checkout in subscription mode).
 */
interface SubscriptionBillingGateway
{
    public function isConfigured(): bool;

    /**
     * Start or change a landlord subscription (Stripe Checkout, mode=subscription).
     */
    public function createSubscriptionCheckout(User $landlord): CheckoutSessionResult;

    /**
     * Stripe Customer Portal for invoices, payment method, cancellation.
     */
    public function createCustomerPortalUrl(User $landlord): string;
}
