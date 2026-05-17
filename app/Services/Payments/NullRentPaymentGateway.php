<?php

namespace App\Services\Payments;

use App\Contracts\Payments\RentPaymentGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\PaymentHistory;
use App\Models\Tenant;

class NullRentPaymentGateway implements RentPaymentGateway
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function createRentCheckout(PaymentHistory $payment, Tenant $tenant): CheckoutSessionResult
    {
        throw StripeNotConfiguredException::forFeature('rent checkout');
    }
}
