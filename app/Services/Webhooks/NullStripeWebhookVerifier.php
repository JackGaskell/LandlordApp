<?php

namespace App\Services\Webhooks;

use App\Contracts\Webhooks\StripeWebhookVerifier;
use Illuminate\Http\Request;

/**
 * Local/testing fallback when STRIPE_WEBHOOK_SECRET is not set.
 */
class NullStripeWebhookVerifier implements StripeWebhookVerifier
{
    public function verify(Request $request): void
    {
        // Intentionally no-op for local development without Stripe CLI.
    }
}
