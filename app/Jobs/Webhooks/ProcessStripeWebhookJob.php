<?php

namespace App\Jobs\Webhooks;

use App\Models\StripeWebhookEvent;
use App\Services\Webhooks\StripeWebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Processes a verified Stripe webhook asynchronously.
 */
class ProcessStripeWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [10, 30, 60, 120, 300];

    /**
     * @param  array<string, mixed>  $payload  Decoded Stripe event (type, data, etc.)
     */
    public function __construct(
        public string $stripeEventId,
        public string $eventType,
        public array $payload,
    ) {
        $this->onQueue(config('landlord.queues.webhooks'));
    }

    public function handle(StripeWebhookDispatcher $dispatcher): void
    {
        $event = StripeWebhookEvent::query()->firstOrCreate(
            ['stripe_event_id' => $this->stripeEventId],
            [
                'type' => $this->eventType,
                'payload' => $this->payload,
            ],
        );

        if ($event->isProcessed()) {
            return;
        }

        $dispatcher->dispatch($this->eventType, $this->payload);

        $event->markProcessed();
    }
}
