<?php

namespace App\Services\Webhooks;

use App\Contracts\Webhooks\StripeWebhookHandler;
use Illuminate\Support\Facades\Log;

class StripeWebhookDispatcher
{
    /**
     * @param  iterable<int, StripeWebhookHandler>  $handlers
     */
    public function __construct(
        protected iterable $handlers,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventType, array $payload): void
    {
        $handled = false;

        foreach ($this->handlers as $handler) {
            if (! $handler->handles($eventType)) {
                continue;
            }

            $handler->handle($payload);
            $handled = true;
        }

        if (! $handled) {
            Log::debug('Stripe webhook ignored (no handler)', ['type' => $eventType]);
        }
    }
}
