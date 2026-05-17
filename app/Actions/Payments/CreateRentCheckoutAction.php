<?php

namespace App\Actions\Payments;

use App\Contracts\Payments\RentPaymentGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Models\PaymentHistory;

/**
 * Creates a Stripe Checkout session for one rent period and stores the session id.
 */
class CreateRentCheckoutAction
{
    public function __construct(
        protected RentPaymentGateway $rentPayments,
    ) {}

    public function execute(PaymentHistory $payment): CheckoutSessionResult
    {
        $payment->loadMissing('tenant');

        $session = $this->rentPayments->createRentCheckout($payment, $payment->tenant);

        $payment->update([
            'stripe_checkout_session_id' => $session->sessionId,
        ]);

        return $session;
    }
}
