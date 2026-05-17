<?php

namespace App\DataTransferObjects\Billing;

/**
 * Returned when creating a Stripe Checkout session (subscription or rent).
 */
readonly class CheckoutSessionResult
{
    public function __construct(
        public string $sessionId,
        public string $url,
    ) {}
}
