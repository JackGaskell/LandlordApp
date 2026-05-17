<?php

namespace App\Http\Controllers;

use App\Contracts\Webhooks\StripeWebhookVerifier;
use App\Jobs\Webhooks\ProcessStripeWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe webhooks: verify signature, acknowledge fast, process on queue.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookVerifier $verifier): Response
    {
        $verifier->verify($request);

        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        ProcessStripeWebhookJob::dispatch(
            stripeEventId: (string) ($payload['id'] ?? 'unknown'),
            eventType: (string) ($payload['type'] ?? 'unknown'),
            payload: $payload,
        );

        return response('Webhook received', 200);
    }
}
