<?php

namespace App\Contracts\Webhooks;

interface StripeWebhookHandler
{
    public function handles(string $eventType): bool;

    /**
     * @param  array<string, mixed>  $payload  Full Stripe event object.
     */
    public function handle(array $payload): void;
}
