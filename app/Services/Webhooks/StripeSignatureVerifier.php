<?php

namespace App\Services\Webhooks;

use App\Contracts\Webhooks\StripeWebhookVerifier;
use App\Exceptions\StripeNotConfiguredException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Validates Stripe-Signature using stripe/stripe-php when a webhook secret is configured.
 */
class StripeSignatureVerifier implements StripeWebhookVerifier
{
    public function verify(Request $request): void
    {
        $secret = config('services.stripe.webhook_secret');

        if (blank($secret)) {
            return;
        }

        if (! class_exists(\Stripe\Webhook::class)) {
            throw StripeNotConfiguredException::forFeature('webhook signature verification');
        }

        $signature = $request->header('Stripe-Signature');

        if (blank($signature)) {
            throw new AccessDeniedHttpException('Missing Stripe-Signature header.');
        }

        try {
            \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret,
                config('landlord.stripe.webhook_tolerance', 300),
            );
        } catch (\UnexpectedValueException|\Stripe\Exception\SignatureVerificationException $e) {
            throw new AccessDeniedHttpException('Invalid Stripe webhook signature.', $e);
        }
    }
}
