<?php

namespace App\Exceptions;

use RuntimeException;

class StripeNotConfiguredException extends RuntimeException
{
    public static function forFeature(string $feature): self
    {
        return new self(
            "Stripe is not configured for [{$feature}]. Set STRIPE_ENABLED=true and add API keys, or run: composer require stripe/stripe-php",
        );
    }
}
