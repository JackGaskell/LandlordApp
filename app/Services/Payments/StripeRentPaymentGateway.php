<?php

namespace App\Services\Payments;

use App\Contracts\Payments\RentPaymentGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\PaymentHistory;
use App\Models\Tenant;

/**
 * Stripe Checkout for a single rent period (mode=payment).
 *
 * createRentCheckout() should:
 * - line_items: amount in smallest currency unit, description "Rent – {tenant}"
 * - metadata: { type: "rent_payment", payment_history_id, tenant_id, landlord_user_id }
 * - customer_email: tenant email (optional)
 * - Connect: use stripe_account on the session when landlords have connected accounts
 */
class StripeRentPaymentGateway implements RentPaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'))
            && class_exists(\Stripe\StripeClient::class);
    }

    public function createRentCheckout(PaymentHistory $payment, Tenant $tenant): CheckoutSessionResult
    {
        if (! $this->isConfigured()) {
            throw StripeNotConfiguredException::forFeature('rent checkout');
        }

        // TODO: \Stripe\Checkout\Session::create([...])
        throw StripeNotConfiguredException::forFeature('rent checkout (not implemented)');
    }
}
