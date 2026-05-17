<?php

namespace App\Contracts\Payments;

use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Models\PaymentHistory;
use App\Models\Tenant;

/**
 * Tenant → landlord rent collection (Stripe Checkout in payment mode).
 */
interface RentPaymentGateway
{
    public function isConfigured(): bool;

    /**
     * One-off rent payment for a specific payment period.
     */
    public function createRentCheckout(PaymentHistory $payment, Tenant $tenant): CheckoutSessionResult;
}
